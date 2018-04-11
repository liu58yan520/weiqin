<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
class Index_EweiShopV2Page extends MobileLoginPage 
{
	public function main() 
	{
		global $_W;
		global $_GPC;
		$this->diypage('member');
		$member = m('member')->getMember($_W['openid'], true);
		$level = m('member')->getLevel($_W['openid']);
		$open_creditshop = p('creditshop') && $_W['shopset']['creditshop']['centeropen'];
		$params = array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']);
		$merch_plugin = p('merch');
		$merch_data = m('common')->getPluginset('merch');
		if ($merch_plugin && $merch_data['is_openmerch']) 
		{
			$statics = array('order_0' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and status=0 and (isparent=1 or (isparent=0 and parentid=0)) and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'order_1' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and (status=1 or (status=0 and paytype=3)) and isparent=0 and refundid=0 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'order_2' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and (status=2 or (status=1 and sendtype>0)) and isparent=0 and refundid=0 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'order_4' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and refundstate=1 and isparent=0 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'cart' => pdo_fetchcolumn('select ifnull(sum(total),0) from ' . tablename('ewei_shop_member_cart') . ' where uniacid=:uniacid and openid=:openid and deleted=0', $params), 'favorite' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_member_favorite') . ' where uniacid=:uniacid and openid=:openid and deleted=0', $params));
		}
		else 
		{
			$statics = array('order_0' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and status=0 and isparent=0 and paytype<>3 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'order_1' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and (status=1 or (status=0 and paytype=3)) and isparent=0 and refundid=0 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'order_2' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and (status=2 or (status=1 and sendtype>0)) and isparent=0 and refundid=0 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'order_4' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and refundstate=1 and isparent=0 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'cart' => pdo_fetchcolumn('select ifnull(sum(total),0) from ' . tablename('ewei_shop_member_cart') . ' where uniacid=:uniacid and openid=:openid and deleted=0 and selected = 1', $params), 'favorite' => ($merch_plugin && $merch_data['is_openmerch'] ? pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_member_favorite') . ' where uniacid=:uniacid and openid=:openid and deleted=0 and `type`=0', $params) : pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_member_favorite') . ' where uniacid=:uniacid and openid=:openid and deleted=0', $params)));
		}
		$newstore_plugin = p('newstore');
		if ($newstore_plugin) 
		{
			$statics['norder_0'] = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and status=0 and isparent=0 and istrade=1 and uniacid=:uniacid', $params);
			$statics['norder_1'] = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and status=1 and isparent=0 and istrade=1 and refundid=0 and uniacid=:uniacid', $params);
			$statics['norder_3'] = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and status=3 and isparent=0 and istrade=1 and uniacid=:uniacid', $params);
			$statics['norder_4'] = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and refundstate=1 and isparent=0 and istrade=1 and uniacid=:uniacid', $params);
		}
		$hascoupon = false;
		$hascouponcenter = false;
		$plugin_coupon = com('coupon');
		if ($plugin_coupon) 
		{
			$time = time();
			$sql = 'select count(*) from ' . tablename('ewei_shop_coupon_data') . ' d';
			$sql .= ' left join ' . tablename('ewei_shop_coupon') . ' c on d.couponid = c.id';
			$sql .= ' where d.openid=:openid and d.uniacid=:uniacid and  d.used=0 ';
			$sql .= ' and (   (c.timelimit = 0 and ( c.timedays=0 or c.timedays*86400 + d.gettime >=unix_timestamp() ) )  or  (c.timelimit =1 and c.timestart<=' . $time . ' && c.timeend>=' . $time . ')) order by d.gettime desc';
			$statics['coupon'] = pdo_fetchcolumn($sql, array(':openid' => $_W['openid'], ':uniacid' => $_W['uniacid']));
			$pcset = $_W['shopset']['coupon'];
			if (empty($pcset['closemember'])) 
			{
				$hascoupon = true;
			}
			if (empty($pcset['closecenter'])) 
			{
				$hascouponcenter = true;
			}
		}
		$hasglobonus = false;
		$plugin_globonus = p('globonus');
		if ($plugin_globonus) 
		{
			$plugin_globonus_set = $plugin_globonus->getSet();
			$hasglobonus = !(empty($plugin_globonus_set['open'])) && !(empty($plugin_globonus_set['openmembercenter']));
		}
		$haslive = false;
		$haslive = p('live');
		if ($haslive) 
		{
			$live_set = $haslive->getSet();
			$haslive = $live_set['ismember'];
		}
		$hasThreen = false;
		$hasThreen = p('threen');
		if ($hasThreen) 
		{
			$plugin_threen_set = $hasThreen->getSet();
			$hasThreen = !(empty($plugin_threen_set['open'])) && !(empty($plugin_threen_set['threencenter']));
		}
		$hasauthor = false;
		$plugin_author = p('author');
		if ($plugin_author) 
		{
			$plugin_author_set = $plugin_author->getSet();
			$hasauthor = !(empty($plugin_author_set['open'])) && !(empty($plugin_author_set['openmembercenter']));
		}
		$hasabonus = false;
		$plugin_abonus = p('abonus');
		if ($plugin_abonus) 
		{
			$plugin_abonus_set = $plugin_abonus->getSet();
			$hasabonus = !(empty($plugin_abonus_set['open'])) && !(empty($plugin_abonus_set['openmembercenter']));
		}
		$card = m('common')->getSysset('membercard');
		$actionset = m('common')->getSysset('memberCardActivation');
		$sql = 'select vg.*,g.title,g.subtitle,g.thumb,c.card_id  from ' . tablename('ewei_shop_verifygoods') . '   vg' . "\r\n\t\t" . ' inner join ' . tablename('ewei_shop_order_goods') . ' og on vg.ordergoodsid = og.id' . "\r\n\t\t" . ' left  join ' . tablename('ewei_shop_order') . ' o on vg.orderid = o.id' . "\r\n\t\t" . ' left  join ' . tablename('ewei_shop_order_refund') . ' orf on o.refundid = orf.id' . "\r\n\t\t" . ' inner join ' . tablename('ewei_shop_goods') . ' g on og.goodsid = g.id' . "\r\n\t\t" . ' left  join ' . tablename('ewei_shop_goods_cards') . ' c on c.id = g.cardid' . "\r\n\t\t" . ' where   vg.uniacid=:uniacid and vg.openid=:openid and vg.invalid =0 and (o.refundid=0 or orf.status<0) and o.status>0' . "\r\n\t\t" . ' and    ((vg.limittype=0   and vg.limitdays * 86400 + vg.starttime >=unix_timestamp() )or ( vg.limittype=1   and vg.limitdate >=unix_timestamp() ))  and  vg.used =0 order by vg.starttime desc';
		$verifygoods = set_medias(pdo_fetchall($sql, array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid'])), 'thumb');
		if (empty($verifygoods)) 
		{
			$verifygoods = array();
		}
		foreach ($verifygoods as $i => &$row ) 
		{
			$row['numlimit'] = 0;
			if (!(empty($row['limitnum']))) 
			{
				$row['numlimit'] = 1;
			}
			if (is_weixin()) 
			{
				if (!(empty($row['card_id'])) && empty($row['activecard'])) 
				{
					$row['cangetcard'] = 1;
				}
			}
		}
		unset($row);
		$showcard = 0;
		if (!(empty($card))) 
		{
			$membercardid = $member['membercardid'];
			if (!(empty($membercardid)) && ($card['card_id'] == $membercardid)) 
			{
				$cardtag = '查看微信会员卡信息';
				$showcard = 1;
			}
			else if (!(empty($actionset['centerget']))) 
			{
				$showcard = 1;
				$cardtag = '领取微信会员卡';
			}
		}
		$hasqa = false;
		$plugin_qa = p('qa');
		if ($plugin_qa) 
		{
			$plugin_qa_set = $plugin_qa->getSet();
			if (!(empty($plugin_qa_set['showmember']))) 
			{
				$hasqa = true;
			}
		}
		$hassign = false;
		$com_sign = p('sign');
		if ($com_sign) 
		{
			$com_sign_set = $com_sign->getSet();
			if (!(empty($com_sign_set['iscenter'])) && !(empty($com_sign_set['isopen']))) 
			{
				$hassign = ((empty($_W['shopset']['trade']['credittext']) ? '积分' : $_W['shopset']['trade']['credittext']));
				$hassign .= ((empty($com_sign_set['textsign']) ? '签到' : $com_sign_set['textsign']));
			}
		}
		$hasLineUp = false;
		$lineUp = p('lineup');
		if ($lineUp) 
		{
			$lineUpSet = $lineUp->getSet();
			if (!(empty($lineUpSet['isopen'])) && !(empty($lineUpSet['mobile_show']))) 
			{
				$hasLineUp = true;
			}
		}
		$wapset = m('common')->getSysset('wap');
		$appset = m('common')->getSysset('app');
		$needbind = false;
		if (empty($member['mobileverify']) || empty($member['mobile'])) 
		{
			if ((empty($_W['shopset']['app']['isclose']) && !(empty($_W['shopset']['app']['openbind']))) || !(empty($_W['shopset']['wap']['open'])) || $hasThreen) 
			{
				$needbind = true;
			}
		}
		if (p('mmanage')) 
		{
			$roleuser = pdo_fetch('SELECT id, uid, username, status FROM' . tablename('ewei_shop_perm_user') . 'WHERE openid=:openid AND uniacid=:uniacid AND status=1 LIMIT 1', array(':openid' => $_W['openid'], ':uniacid' => $_W['uniacid']));
		}
		include $this->template();
	}
}
?>