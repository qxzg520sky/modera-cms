<?php
declare(strict_types=1);

namespace plugins\multilang\controller;

use app\controller\Index;

/**
 * 多语言前台：/en/ 、/en/c/:slug 、/en/a/:id 、/en/p/:slug 、/en/search
 * 内容读取、模板渲染全部复用主程序 Index，只是 current_lang() 变成 en，
 * 从而自动取 name_en / title_en 等字段，并把界面文案换成英文。
 */
class Front extends \app\BaseController
{
    public function index()
    {
        return (new Index($this->app))->index();
    }

    public function lists(string $slug = '')
    {
        return (new Index($this->app))->lists($slug);
    }

    public function show(int $id = 0)
    {
        return (new Index($this->app))->show($id);
    }

    public function page(string $slug = '')
    {
        return (new Index($this->app))->page($slug);
    }

    public function search()
    {
        return (new Index($this->app))->search();
    }
}
