<?php
declare(strict_types=1);

namespace plugins\kefu\controller;

use plugins\kefu\service\Kefu;
use think\facade\Db;

/**
 * 在线客服前台接口（无需登录）
 * POST /kefu/api/send    访客发消息 / 首次留言
 * GET  /kefu/api/poll    访客轮询客服回复
 * GET  /kefu/asset/:file 插件自己的 css / js
 */
class Api
{
    /** 插件静态资源 */
    public function asset(string $file = '')
    {
        $allow = ['kefu.css' => 'text/css; charset=utf-8', 'kefu.js' => 'application/javascript; charset=utf-8'];
        if (!isset($allow[$file])) {
            return response('', 404);
        }
        $path = dirname(__DIR__) . '/asset/' . $file;
        if (!is_file($path)) {
            return response('', 404);
        }
        return response((string) file_get_contents($path))
            ->header([
                'Content-Type'  => $allow[$file],
                'Cache-Control' => 'public, max-age=3600',
            ]);
    }

    /** 访客发消息（首次发送会自动建会话） */
    public function send()
    {
        $cfg = Kefu::cfg();
        if (($cfg['enabled'] ?? '1') !== '1') {
            return json(['ok' => false, 'msg' => '客服功能已关闭']);
        }

        $content = trim((string) request()->post('content', ''));
        $token   = trim((string) request()->post('token', ''));
        $name    = trim((string) request()->post('name', ''));
        $contact = trim((string) request()->post('contact', ''));
        $page    = mb_substr(trim((string) request()->post('page', '')), 0, 200);

        $content = strip_tags($content);
        if ($content === '') {
            return json(['ok' => false, 'msg' => '请先写点什么']);
        }
        $max = (int) ($cfg['max_len'] ?: 500);
        if (mb_strlen($content) > $max) {
            $content = mb_substr($content, 0, $max);
        }

        $row  = null;
        $addr = request()->ip();

        if ($token !== '' && preg_match('/^[a-f0-9]{32}$/', $token)) {
            $row = Db::name(Kefu::T_SESSION)->where('token', $token)->find();
        }

        // 需要留联系方式但还没有会话，且本次没填 → 让前台弹出联系表单
        if (!$row && ($cfg['require_contact'] ?? '1') === '1' && $contact === '') {
            return json(['ok' => false, 'need_contact' => 1, 'msg' => '请留下称呼和联系方式，方便客服回复您']);
        }

        $now = time();
        if (!$row) {
            $token = bin2hex(random_bytes(16));
            $id = (int) Db::name(Kefu::T_SESSION)->insertGetId([
                'token'       => $token,
                'name'        => mb_substr($name !== '' ? $name : (string) $cfg['guest_default'], 0, 20),
                'contact'     => mb_substr($contact, 0, 60),
                'page'        => $page,
                'ip'          => (string) $addr,
                'ua'          => mb_substr((string) request()->header('user-agent', ''), 0, 200),
                'status'      => 0,
                'unread'      => 0,
                'admin_unread' => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $row = Db::name(Kefu::T_SESSION)->where('id', $id)->find();

            // 建会话时的自动回复 + 欢迎语
            $auto = trim((string) $cfg['auto_reply']);
            Db::name(Kefu::T_MESSAGE)->insert([
                'session_id' => $id,
                'role'       => 'system',
                'content'    => $auto !== '' ? $auto : '已收到您的留言，客服会尽快回复。',
                'is_read'    => 0,
                'created_at' => $now,
            ]);
        } else {
            $id = (int) $row['id'];
            // 更新联系方式（访客可能后来补填）
            $upd = ['updated_at' => $now, 'status' => 0, 'admin_unread' => (int) $row['admin_unread'] + 1];
            if ($contact !== '' && (string) $row['contact'] === '') {
                $upd['contact'] = mb_substr($contact, 0, 60);
            }
            if ($name !== '' && in_array((string) $row['name'], ['访客', (string) $cfg['guest_default']], true)) {
                $upd['name'] = mb_substr($name, 0, 20);
            }
            Db::name(Kefu::T_SESSION)->where('id', $id)->update($upd);

            // 简易防刷：同一会话 10 秒内最多 5 条
            $recent = Db::name(Kefu::T_MESSAGE)
                ->where('session_id', $id)->where('role', 'visitor')
                ->where('created_at', '>=', $now - 10)->count();
            if ($recent >= 5) {
                return json(['ok' => false, 'msg' => '发送太快了，请稍等一下']);
            }
        }

        $mid = (int) Db::name(Kefu::T_MESSAGE)->insertGetId([
            'session_id' => $id,
            'role'       => 'visitor',
            'content'    => $content,
            'is_read'    => 0,
            'created_at' => $now,
        ]);

        return json([
            'ok'      => true,
            'token'   => $token,
            'id'      => $mid,
            'online'  => Kefu::onlineNow($cfg),
            'msg'     => '已发送',
        ]);
    }

    /** 访客轮询：取 after 之后客服/系统消息 */
    public function poll()
    {
        $cfg   = Kefu::cfg();
        $token = trim((string) request()->get('token', ''));
        $after = (int) request()->get('after', 0);

        $out = ['ok' => true, 'online' => Kefu::onlineNow($cfg), 'messages' => [], 'last' => $after];
        if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
            return json($out);
        }
        $row = Db::name(Kefu::T_SESSION)->where('token', $token)->find();
        if (!$row) {
            return json($out + ['gone' => 1]);
        }
        $sid = (int) $row['id'];

        $msgs = Db::name(Kefu::T_MESSAGE)
            ->where('session_id', $sid)
            ->where('id', '>', $after)
            ->order('id asc')->limit(50)->select()->toArray();

        $hasAdmin = false;
        foreach ($msgs as $m) {
            $out['messages'][] = [
                'id'      => (int) $m['id'],
                'role'    => (string) $m['role'],
                'content' => (string) $m['content'],
                'time'    => date('H:i', (int) $m['created_at']),
            ];
            $out['last'] = (int) $m['id'];
            if ($m['role'] !== 'visitor') {
                $hasAdmin = true;
            }
        }
        if ($hasAdmin) {
            Db::name(Kefu::T_SESSION)->where('id', $sid)->update(['unread' => 0]);
        }
        // 后台正在看的会话，刷新时告诉访客会话是否被处理
        $out['closed'] = (int) $row['status'] === 2;
        return json($out);
    }
}
