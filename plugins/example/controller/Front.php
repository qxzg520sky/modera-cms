<?php
declare(strict_types=1);

namespace plugins\example\controller;

use think\facade\Db;

class Front
{
    public function index()
    {
        $row = Db::name('plugin')->where('name', 'example')->find();
        $cfg = $row && !empty($row['config']) ? (json_decode((string) $row['config'], true) ?: []) : [];
        $text = $cfg['welcome_text'] ?? '这是示例插件输出的内容';
        return '<div style="font:16px/2 system-ui;max-width:720px;margin:80px auto;padding:32px;border:1px solid #e5e7eb;border-radius:12px">'
            . '<h2 style="margin:0 0 12px">示例插件已生效</h2>'
            . '<p style="color:#6b7280;margin:0">' . htmlspecialchars($text) . '</p>'
            . '<p style="color:#9ca3af;font-size:13px;margin:16px 0 0">此页面由 plugins/example 提供，停用插件即消失。</p>'
            . '</div>';
    }
}
