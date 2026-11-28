<?php
declare(strict_types=1);

namespace plugins\shop\controller;

use app\BaseController;
use plugins\shop\service\Shop;
use think\facade\Db;

/**
 * 商城业务员订单：复用会员插件业务员登录态（session member_staff_id）。
 * 只能看 staff_id = 当前业务员 的订单。
 */
class Staff extends BaseController
{
    private function staff(): array
    {
        try {
            return \plugins\member\service\Member::currentStaff() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function orders()
    {
        $staff = $this->staff();
        if (!$staff) return redirect('/member/staff/login');
        $status = (string) $this->request->get('status', '');
        $q = Db::name(Shop::T_ORDER)->where('staff_id', (int) $staff['id']);
        if ($status !== '') $q->where('status', $status);
        $total = (int) (clone $q)->count();
        $page = max(1, (int) $this->request->get('page', 1));
        $pages = max(1, (int) ceil($total / 15));
        $list = $q->order('id desc')->page(min($page, $pages), 15)->select()->toArray();
        $sum = round((float) Db::name(Shop::T_ORDER)->where('staff_id', (int) $staff['id'])->where('pay_status', 1)->where('status', '<>', 'refunded')->sum('payable_amount'), 2);
        return \app\service\View::fetch(dirname(__DIR__) . '/view/staff/orders.php', [
            'pageTitle' => '我的客户订单',
            'staff' => $staff,
            'list' => $list,
            'f' => ['status' => $status],
            'pager' => ['total' => $total, 'page' => min($page, $pages), 'pages' => $pages],
            'sum' => $sum,
            'cfg' => Shop::cfg(),
        ]);
    }
}
