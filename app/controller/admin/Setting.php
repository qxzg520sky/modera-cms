<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\model\Setting as SettingModel;

class Setting extends Base
{
    public function index()
    {
        return $this->view('setting/index', [
            'active'    => 'setting',
            'pageTitle' => '系统设置',
            's'         => SettingModel::allSettings(),
        ]);
    }

    public function save()
    {
        $fields = [
            'site_name', 'site_slogan', 'site_keywords', 'site_description', 'site_logo',
            'site_icp', 'site_copyright', 'site_phone', 'site_email', 'site_address',
            'article_page_size', 'mobile_enable',
        ];
        $data = [];
        foreach ($fields as $field) {
            $data[$field] = (string) $this->request->post($field, '');
        }
        if ($data['site_name'] === '') {
            $data['site_name'] = '我的企业网站';
        }
        SettingModel::saveMany($data);
        return $this->back('/admin/setting', '设置已保存');
    }
}

