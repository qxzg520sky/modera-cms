<?php
declare(strict_types=1);

namespace plugins\slider\controller;

use plugins\slider\service\Slider;
use think\facade\Db;

/**
 * 轮播广告后台：列表 / 新增编辑 / 启停 / 排序 / 删除
 */
class Admin extends \app\controller\admin\Base
{
    protected function render(string $tpl, array $data = []): string
    {
        $flash = session('flash_msg');
        if ($flash !== null && $flash !== '') {
            session('flash_msg', null);
        }
        $data['admin']  = $this->admin;
        $data['flash']  = (string) $flash;
        $data['active'] = $data['active'] ?? 'plugin-' . md5('/admin/plugin/slider');
        return \app\service\View::fetch(__DIR__ . '/../view/' . $tpl . '.php', $data);
    }

    public function index()
    {
        $slides = Slider::all();
        $rows   = [];
        foreach ($slides as $s) {
            $rows[] = Slider::normalize($s) + [
                'status' => (int) $s['status'],
                'sort'   => (int) $s['sort'],
                'raw_link'  => (string) $s['link'],
                'raw_link2' => (string) $s['link2'],
            ];
        }

        $theme = '';
        try {
            $theme = (string) setting('theme', 'default');
        } catch (\Throwable $e) {
        }

        return $this->render('index', [
            'pageTitle' => '轮播广告',
            'slides'    => $rows,
            'theme'     => $theme,
            'enabled'   => count(array_filter($rows, function ($r) {
                return $r['status'] === 1;
            })),
        ]);
    }

    public function form()
    {
        $id  = (int) $this->request->param('id', 0);
        $row = [
            'id' => 0, 'title' => '', 'subtitle' => '', 'summary' => '', 'image' => '',
            'link' => '', 'link_text' => '查看详情', 'link2' => '', 'link2_text' => '更多栏目',
            'target' => '_self', 'title_en' => '', 'subtitle_en' => '', 'summary_en' => '',
            'link_text_en' => '', 'link2_text_en' => '', 'sort' => 0, 'status' => 1,
        ];
        if ($id) {
            $found = Db::name(Slider::T_SLIDE)->where('id', $id)->find();
            if (!$found) {
                return $this->back('/admin/plugin/slider', '广告不存在');
            }
            $row = $found;
        } else {
            $max = 0;
            try {
                $max = (int) Db::name(Slider::T_SLIDE)->max('sort');
            } catch (\Throwable $e) {
            }
            $row['sort'] = $max + 1;
        }

        return $this->render('form', [
            'pageTitle' => $id ? '编辑广告' : '新增广告',
            'row'       => $row,
        ]);
    }

    public function save()
    {
        $id   = (int) $this->request->post('id', 0);
        $data = [
            'title'        => mb_substr(trim((string) $this->request->post('title', '')), 0, 120),
            'subtitle'     => mb_substr(trim((string) $this->request->post('subtitle', '')), 0, 160),
            'summary'      => mb_substr(trim((string) $this->request->post('summary', '')), 0, 255),
            'image'        => mb_substr(trim((string) $this->request->post('image', '')), 0, 255),
            'link'         => mb_substr(trim((string) $this->request->post('link', '')), 0, 255),
            'link_text'    => mb_substr(trim((string) $this->request->post('link_text', '')), 0, 40),
            'link2'        => mb_substr(trim((string) $this->request->post('link2', '')), 0, 255),
            'link2_text'   => mb_substr(trim((string) $this->request->post('link2_text', '')), 0, 40),
            'target'       => (string) $this->request->post('target', '_self') === '_blank' ? '_blank' : '_self',
            'title_en'     => mb_substr(trim((string) $this->request->post('title_en', '')), 0, 160),
            'subtitle_en'  => mb_substr(trim((string) $this->request->post('subtitle_en', '')), 0, 160),
            'summary_en'   => mb_substr(trim((string) $this->request->post('summary_en', '')), 0, 255),
            'link_text_en' => mb_substr(trim((string) $this->request->post('link_text_en', '')), 0, 40),
            'link2_text_en' => mb_substr(trim((string) $this->request->post('link2_text_en', '')), 0, 40),
            'sort'         => (int) $this->request->post('sort', 0),
            'status'       => (int) $this->request->post('status', 1) === 1 ? 1 : 0,
            'updated_at'   => time(),
        ];

        if ($data['image'] === '') {
            return $this->back($id ? '/admin/plugin/slider/form/' . $id : '/admin/plugin/slider/form', '请先上传或填写广告图片');
        }
        if ($data['title'] === '') {
            return $this->back($id ? '/admin/plugin/slider/form/' . $id : '/admin/plugin/slider/form', '广告标题不能为空');
        }

        if ($id) {
            Db::name(Slider::T_SLIDE)->where('id', $id)->update($data);
            return $this->back('/admin/plugin/slider', '广告已更新');
        }
        $data['created_at'] = time();
        Db::name(Slider::T_SLIDE)->insert($data);
        return $this->back('/admin/plugin/slider', '广告已添加');
    }

    public function toggle()
    {
        $id = (int) $this->request->post('id', 0);
        if (!$id) {
            return $this->back('/admin/plugin/slider', '参数不完整');
        }
        $row = Db::name(Slider::T_SLIDE)->where('id', $id)->find();
        if (!$row) {
            return $this->back('/admin/plugin/slider', '广告不存在');
        }
        $to = (int) $row['status'] === 1 ? 0 : 1;
        Db::name(Slider::T_SLIDE)->where('id', $id)->update([
            'status'     => $to,
            'updated_at' => time(),
        ]);
        return $this->back('/admin/plugin/slider', $to ? '广告已启用' : '广告已停用（前台不再显示这一条）');
    }

    public function remove()
    {
        $id = (int) $this->request->post('id', 0);
        if ($id) {
            Db::name(Slider::T_SLIDE)->where('id', $id)->delete();
            Slider::renumber();
        }
        return $this->back('/admin/plugin/slider', '广告已删除');
    }

    public function move()
    {
        $id  = (int) $this->request->post('id', 0);
        $dir = (string) $this->request->post('dir', 'up');
        $ok  = $id ? Slider::move($id, $dir === 'down' ? 'down' : 'up') : false;
        return $this->back('/admin/plugin/slider', $ok ? '顺序已调整' : '已经是第一条/最后一条，无法再移动');
    }
}
