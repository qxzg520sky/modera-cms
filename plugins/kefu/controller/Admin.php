<?php
declare(strict_types=1);

namespace plugins\kefu\controller;

use plugins\kefu\service\Kefu;
use think\facade\Db;

/**
 * 在线客服后台：坐席管理 / 会话收件箱 / 插件设置
 */
class Admin extends \app\controller\admin\Base
{
    /** 用插件自己的模板渲染，套用后台公共框架 */
    protected function render(string $tpl, array $data = []): string
    {
        $flash = session('flash_msg');
        if ($flash !== null && $flash !== '') {
            session('flash_msg', null);
        }
        $data['admin']  = $this->admin;
        $data['flash']  = (string) $flash;
        $data['active'] = $data['active'] ?? '';
        return \app\service\View::fetch(__DIR__ . '/../view/' . $tpl . '.php', $data);
    }

    /* ==================== 坐席 ==================== */

    public function index()
    {
        // 待回复留言（最多 5 条），直接摆在坐席页最上面，避免要翻菜单才看得到
        $pending = [];
        $pendingLast = [];
        try {
            $pending = Db::name(Kefu::T_SESSION)->where('admin_unread', '>', 0)
                ->order('updated_at desc, id desc')->limit(5)->select()->toArray();
            foreach ($pending as $p) {
                $pendingLast[(int) $p['id']] = Db::name(Kefu::T_MESSAGE)
                    ->where('session_id', (int) $p['id'])
                    ->where('role', 'visitor')
                    ->order('id desc')->find() ?: [];
            }
        } catch (\Throwable $e) {
        }

        return $this->render('admin', [
            'active'      => 'plugin-' . md5('/admin/plugin/kefu'),
            'pageTitle'   => '客服坐席',
            'staff'       => Kefu::staff(false),
            'stats'       => Kefu::stats(),
            'cfg'         => Kefu::cfg(),
            'types'       => Kefu::TYPES,
            'online'      => Kefu::onlineNow(),
            'pending'     => $pending,
            'pendingLast' => $pendingLast,
            'badge'       => (int) (Kefu::stats()['unread'] ?? 0),
        ]);
    }

    public function staffEdit()
    {
        $id = (int) $this->request->param('id', 0);
        $row = [];
        if ($id) {
            $row = Db::name(Kefu::T_STAFF)->where('id', $id)->find() ?: [];
        }
        return $this->render('staff_edit', [
            'active'    => 'plugin-' . md5('/admin/plugin/kefu'),
            'pageTitle' => $id ? '编辑坐席' : '新增坐席',
            'row'       => $row,
            'types'     => Kefu::TYPES,
        ]);
    }

    public function staffSave()
    {
        $id = (int) $this->request->post('id', 0);
        $data = [
            'name'    => mb_substr(trim((string) $this->request->post('name', '')), 0, 20),
            'title'   => mb_substr(trim((string) $this->request->post('title', '')), 0, 30),
            'type'    => (string) $this->request->post('type', 'online'),
            'account' => mb_substr(trim((string) $this->request->post('account', '')), 0, 120),
            'qr'      => mb_substr(trim((string) $this->request->post('qr', '')), 0, 200),
            'avatar'  => mb_substr(trim((string) $this->request->post('avatar', '')), 0, 200),
            'intro'   => mb_substr(trim((string) $this->request->post('intro', '')), 0, 100),
            'sort'    => (int) $this->request->post('sort', 0),
            'status'  => (int) $this->request->post('status', 1),
        ];
        if ($data['name'] === '') {
            return $this->back('/admin/plugin/kefu', '坐席名称不能为空');
        }
        if (!isset(Kefu::TYPES[$data['type']])) {
            $data['type'] = 'online';
        }
        if ($id) {
            Db::name(Kefu::T_STAFF)->where('id', $id)->update($data);
            return $this->back('/admin/plugin/kefu', '坐席已更新');
        }
        $data['created_at'] = time();
        Db::name(Kefu::T_STAFF)->insert($data);
        return $this->back('/admin/plugin/kefu', '坐席已添加');
    }

    public function staffDelete()
    {
        $id = (int) $this->request->post('id', 0);
        if ($id) {
            Db::name(Kefu::T_STAFF)->where('id', $id)->delete();
        }
        return $this->back('/admin/plugin/kefu', '坐席已删除');
    }

    /* ==================== 后台小接口（供页面自动刷新） ==================== */

    /** 未读数：左下角红点、页面标题、提示音都靠它 */
    public function apiUnread()
    {
        try {
            $st = Kefu::stats();
            return json([
                'ok'       => true,
                'unread'   => (int) $st['unread'],
                'sessions' => (int) $st['sessions'],
                'today'    => (int) $st['today'],
                'time'     => date('H:i:s'),
            ]);
        } catch (\Throwable $e) {
            return json(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }

    /** 某会话 id 之后的新消息（客服开着详情页也能实时看到访客追问） */
    public function apiMessages()
    {
        $id    = (int) $this->request->get('id', 0);
        $after = (int) $this->request->get('after', 0);
        if (!$id) {
            return json(['ok' => false]);
        }
        $row = Db::name(Kefu::T_SESSION)->where('id', $id)->find();
        if (!$row) {
            return json(['ok' => false, 'gone' => 1]);
        }

        $msgs = Db::name(Kefu::T_MESSAGE)->where('session_id', $id)->where('id', '>', $after)
            ->order('id asc')->limit(50)->select()->toArray();

        $items = [];
        foreach ($msgs as $m) {
            $items[] = [
                'id'      => (int) $m['id'],
                'role'    => (string) $m['role'],
                'content' => (string) $m['content'],
                'time'    => date('m-d H:i', (int) $m['created_at']),
            ];
        }
        if ($msgs) {
            Db::name(Kefu::T_SESSION)->where('id', $id)->update(['admin_unread' => 0, 'updated_at' => time()]);
            Db::name(Kefu::T_MESSAGE)->where('session_id', $id)->where('role', 'visitor')->update(['is_read' => 1]);
        }
        $last = $items ? $items[count($items) - 1]['id'] : $after;

        return json([
            'ok'       => true,
            'messages' => $items,
            'last'     => $last,
            'unread'   => (int) (Kefu::stats()['unread'] ?? 0),
            'status'   => (int) $row['status'],
        ]);
    }

    /* ==================== 会话 ==================== */

    public function chat()
    {
        $tab  = (string) $this->request->param('tab', 'all');
        $kw   = trim((string) $this->request->param('kw', ''));
        $q    = Db::name(Kefu::T_SESSION);
        if ($tab === 'unread') {
            $q->where('admin_unread', '>', 0);
        } elseif ($tab === 'closed') {
            $q->where('status', 2);
        } elseif ($tab === 'open') {
            $q->where('status', '<', 2);
        }
        if ($kw !== '') {
            $q->where(function ($qq) use ($kw) {
                $qq->whereLike('name', '%' . $kw . '%')
                    ->whereOr('contact', 'like', '%' . $kw . '%')
                    ->whereOr('page', 'like', '%' . $kw . '%');
            });
        }
        $list = $q->order('updated_at desc, id desc')->limit(60)->select()->toArray();

        // 每个会话最后一条消息
        $lasts = [];
        if ($list) {
            $ids = array_column($list, 'id');
            $rows = Db::name(Kefu::T_MESSAGE)->whereIn('session_id', $ids)->order('id desc')->select()->toArray();
            foreach ($rows as $m) {
                $sid = (int) $m['session_id'];
                if (!isset($lasts[$sid])) {
                    $lasts[$sid] = $m;
                }
            }
        }

        return $this->render('chat', [
            'active'    => 'plugin-' . md5('/admin/plugin/kefu/chat'),
            'pageTitle' => '客服会话',
            'list'      => $list,
            'lasts'     => $lasts,
            'tab'       => $tab,
            'kw'        => $kw,
            'stats'     => Kefu::stats(),
        ]);
    }

    public function chatDetail()
    {
        $id  = (int) $this->request->param('id', 0);
        $row = Db::name(Kefu::T_SESSION)->where('id', $id)->find();
        if (!$row) {
            return $this->back('/admin/plugin/kefu/chat', '会话不存在');
        }
        $msgs = Db::name(Kefu::T_MESSAGE)->where('session_id', $id)->order('id asc')->select()->toArray();

        // 打开即视为已读
        Db::name(Kefu::T_SESSION)->where('id', $id)->update(['admin_unread' => 0]);
        Db::name(Kefu::T_MESSAGE)->where('session_id', $id)->where('role', 'visitor')->update(['is_read' => 1]);

        return $this->render('chat_detail', [
            'active'    => 'plugin-' . md5('/admin/plugin/kefu/chat'),
            'pageTitle' => '会话详情',
            'row'       => $row,
            'msgs'      => $msgs,
        ]);
    }

    public function chatReply()
    {
        $id      = (int) $this->request->post('id', 0);
        $content = trim((string) $this->request->post('content', ''));
        $row     = Db::name(Kefu::T_SESSION)->where('id', $id)->find();
        if (!$row) {
            return $this->back('/admin/plugin/kefu/chat', '会话不存在');
        }
        if ($content === '') {
            return $this->back('/admin/plugin/kefu/chat/' . $id, '回复内容不能为空');
        }
        $now = time();
        Db::name(Kefu::T_MESSAGE)->insert([
            'session_id' => $id,
            'role'       => 'admin',
            'content'    => $content,
            'is_read'    => 0,
            'created_at' => $now,
        ]);
        Db::name(Kefu::T_SESSION)->where('id', $id)->update([
            'status'     => 1,
            'unread'     => (int) $row['unread'] + 1,
            'updated_at' => $now,
        ]);
        return $this->back('/admin/plugin/kefu/chat/' . $id, '已回复，访客下次刷新即可看到');
    }

    public function chatClose()
    {
        $id = (int) $this->request->post('id', 0);
        if ($id) {
            Db::name(Kefu::T_SESSION)->where('id', $id)->update(['status' => 2, 'admin_unread' => 0, 'updated_at' => time()]);
            Db::name(Kefu::T_MESSAGE)->where('session_id', $id)->where('role', 'visitor')->update(['is_read' => 1]);
        }
        return $this->back('/admin/plugin/kefu/chat', '会话已标记为已处理');
    }

    public function chatDelete()
    {
        $id = (int) $this->request->post('id', 0);
        if ($id) {
            Db::name(Kefu::T_MESSAGE)->where('session_id', $id)->delete();
            Db::name(Kefu::T_SESSION)->where('id', $id)->delete();
        }
        return $this->back('/admin/plugin/kefu/chat', '会话及其消息已删除');
    }

    /* ==================== 设置 ==================== */

    public function setting()
    {
        return $this->render('setting', [
            'active'    => 'plugin-' . md5('/admin/plugin/kefu/setting'),
            'pageTitle' => '客服设置',
            'cfg'       => Kefu::cfg(),
            'online'    => Kefu::onlineNow(),
            'stats'     => Kefu::stats(),
        ]);
    }

    public function settingSave()
    {
        $keys = array_keys(Kefu::defaults());
        $in   = [];
        foreach ($keys as $k) {
            $v = $this->request->post($k, null);
            if ($v === null) {
                continue;
            }
            $in[$k] = is_array($v) ? implode(',', array_map('strval', $v)) : trim((string) $v);
        }
        if (!isset($in['enabled'])) {
            $in['enabled'] = '0';
        }
        // 多选工作日
        if ($this->request->post('weekdays') === null) {
            $in['weekdays'] = '';
        }
        Kefu::saveCfg($in);
        return $this->back('/admin/plugin/kefu/setting', '设置已保存');
    }
}
