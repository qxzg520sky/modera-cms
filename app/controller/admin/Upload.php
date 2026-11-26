<?php
declare(strict_types=1);

namespace app\controller\admin;

/**
 * 编辑器图片上传：返回 wangEditor 约定的 {errno, data:{url,alt,href}}
 */
class Upload extends Base
{
    public function image()
    {
        $file = $this->request->file('file');
        if (!$file) {
            return json(['errno' => 1, 'message' => '没有收到文件']);
        }

        $ext = strtolower(pathinfo((string) $file->getOriginalName(), PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return json(['errno' => 1, 'message' => '只允许 jpg / png / gif / webp']);
        }
        if ($file->getSize() > 5 * 1024 * 1024) {
            return json(['errno' => 1, 'message' => '图片不能超过 5MB']);
        }

        $sub = date('Ym');
        $dir = root_path() . 'public/uploads/' . $sub;
        if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
            return json(['errno' => 1, 'message' => '上传目录创建失败']);
        }

        $new = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (!move_uploaded_file($file->getPathname(), $dir . DIRECTORY_SEPARATOR . $new)) {
            return json(['errno' => 1, 'message' => '文件保存失败']);
        }

        return json([
            'errno' => 0,
            'data'  => [
                'url'  => '/uploads/' . $sub . '/' . $new,
                'alt'  => '',
                'href' => '',
            ],
        ]);
    }
}
