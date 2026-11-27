<?php
declare(strict_types=1);

namespace plugins\editor\service;

use think\facade\Db;

/**
 * 编辑器切换插件：WangEditor（默认富文本）/ Vditor（Markdown 模式）
 */
class Editor
{
    public static function config(): array
    {
        try {
            $row = Db::name('plugin')->where('name', 'editor')->find();
            if (!$row || (int) ($row['enabled'] ?? 0) !== 1) {
                return [];
            }
            $cfg = json_decode((string) ($row['config'] ?? ''), true) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
        $type = strtolower((string) ($cfg['editor_type'] ?? 'wangeditor'));
        if (!in_array($type, ['wangeditor', 'vditor'], true)) {
            $type = 'wangeditor';
        }
        return ['editor_type' => $type];
    }

    public static function activeType(): string
    {
        $cfg = self::config();
        return $cfg['editor_type'] ?? 'wangeditor';
    }

    public static function render(string $name, string $value = '', string $height = '460px'): string
    {
        $height = preg_match('/^\d+(?:\.\d+)?(?:px|rem|vh)?$/', $height) ? $height : '460px';
        if (self::activeType() === 'vditor') {
            return self::renderVditor($name, $value, $height);
        }
        return self::renderWang($name, $value, $height);
    }

    private static function renderWang(string $name, string $value, string $height): string
    {
        static $loaded = false;
        static $seq = 0;
        $seq++;
        $id = 'edx_w' . $seq . '_' . preg_replace('/\W/', '_', $name);
        $out = '';
        if (!$loaded) {
            $loaded = true;
            $out .= '<link rel="stylesheet" href="/static/editor/wangeditor/style.css">';
            $out .= '<script src="/static/editor/wangeditor/index.js"></script>';
            $out .= self::style();
        }
        $out .= '<div class="edx-box edx-wang" data-editor="wangeditor">'
            . '<div id="' . $id . '_tb" class="edx-toolbar"></div>'
            . '<div id="' . $id . '_ed" class="edx-body" style="height:' . e($height) . '"></div></div>'
            . '<div class="edx-hint">WangEditor 富文本模式：支持标题、加粗、列表、表格、链接和图片上传。</div>'
            . '<textarea name="' . e($name) . '" id="' . $id . '_ta" style="display:none">' . e($value) . '</textarea>';

        $js = <<<'JS'
var E=window.wangEditor,ed=document.getElementById(__ED__),ta=document.getElementById(__TA__);
if(!E||!ed||!ta)return;
var editor=E.createEditor({selector:__ED_SEL__,html:ta.value||"<p><br></p>",config:{placeholder:"在这里写正文…",MENU_CONF:{uploadImage:{customUpload:function(file,insertFn){var f=new FormData();f.append("file",file);fetch("/admin/upload",{method:"POST",body:f,credentials:"same-origin"}).then(function(r){return r.json()}).then(function(j){if(j&&j.errno===0&&j.data&&j.data.url){insertFn(j.data.url,j.data.alt||"",j.data.href||"")}else{alert((j&&j.message)||"图片上传失败")}}).catch(function(){alert("图片上传失败")})}}}}});
E.createToolbar({editor:editor,selector:__TB_SEL__,config:{excludeKeys:["fullScreen"]}});
if(ta.form){ta.form.addEventListener("submit",function(){ta.value=editor.getHtml()})}
JS;
        $js = str_replace(
            ['__ED__', '__TA__', '__ED_SEL__', '__TB_SEL__'],
            [self::j($id . '_ed'), self::j($id . '_ta'), self::j('#' . $id . '_ed'), self::j('#' . $id . '_tb')],
            $js
        );

        return $out . '<script>(function(){' . $js . '})();</script>';
    }

    private static function renderVditor(string $name, string $value, string $height): string
    {
        static $loaded = false;
        static $seq = 0;
        $seq++;
        $id = 'edx_v' . $seq . '_' . preg_replace('/\W/', '_', $name);
        $out = '';
        if (!$loaded) {
            $loaded = true;
            $out .= '<link rel="stylesheet" href="/static/editor/vditor/dist/index.css">';
            $out .= '<script src="/static/editor/vditor/dist/index.min.js"></script>';
            $out .= '<script src="/static/editor/vendor/turndown.js"></script>';
            $out .= '<script src="/static/editor/vendor/marked.min.js"></script>';
            $out .= self::style();
        }
        $out .= '<div class="edx-box edx-vditor" data-editor="vditor">'
            . '<div id="' . $id . '_ed"></div></div>'
            . '<div class="edx-hint">Vditor Markdown 模式：内容按 Markdown 编辑，提交时自动转换为 HTML，兼容现有文章与单页。</div>'
            . '<textarea name="' . e($name) . '" id="' . $id . '_ta" style="display:none">' . e($value) . '</textarea>';

        $js = <<<'JS'
var mount=document.getElementById(__MOUNT__),ta=document.getElementById(__TA__);
if(!window.Vditor||!mount||!ta)return;
var raw=ta.value||"";
var md=raw;
if(window.TurndownService&&/<[a-z][\s\S]*>/i.test(raw)){try{md=new TurndownService({headingStyle:"atx",codeBlockStyle:"fenced",bulletListMarker:"-"}).turndown(raw)}catch(e){md=raw}}
var vd=new Vditor(mount,{
  cdn:"/static/editor/vditor",
  mode:"ir",
  height:__HEIGHT__,
  value:md,
  lang:"zh_CN",
  cache:{enable:false},
  placeholder:"在这里写 Markdown 正文…",
  toolbarConfig:{pin:true},
  upload:{
    url:"/admin/upload",
    fieldName:"file",
    accept:"image/*",
    max:10*1024*1024,
    format:function(files,response){
      try{
        var j=JSON.parse(response);
        if(j&&j.errno===0&&j.data&&j.data.url){
          var m={};m[files[0].name]=j.data.url;
          return JSON.stringify({msg:"",code:0,data:{errFiles:[],succMap:m}});
        }
      }catch(e){}
      return JSON.stringify({msg:"图片上传失败",code:1,data:{errFiles:files.map(function(f){return f.name}),succMap:{}}});
    }
  }
});
if(ta.form){ta.form.addEventListener("submit",function(){var m=vd.getValue();ta.value=(window.marked&&m)?marked.parse(m):m})}
JS;
        $js = str_replace(
            ['__MOUNT__', '__TA__', '__HEIGHT__'],
            [self::j($id . '_ed'), self::j($id . '_ta'), self::j($height)],
            $js
        );

        return $out . '<script>(function(){' . $js . '})();</script>';
    }

    private static function style(): string
    {
        return '<style>'
            . '.edx-box{border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;background:#fff}'
            . '.edx-toolbar{border-bottom:1px solid #e5e7eb;background:#fafbfc}'
            . '.edx-body{overflow-y:auto}.edx-body .w-e-text-container{background:#fff}'
            . '.edx-vditor .vditor{border:0;border-radius:0}'
            . '.edx-hint{font-size:12px;color:#9aa3af;margin:6px 0 0}'
            . '</style>';
    }

    private static function j(string $v): string
    {
        return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '""';
    }
}
