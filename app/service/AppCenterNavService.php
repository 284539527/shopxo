<?php
// +----------------------------------------------------------------------
// | ShopXO 国内领先企业级B2C免费开源电商系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011~2099 http://shopxo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( https://opensource.org/licenses/mit-license.php )
// +----------------------------------------------------------------------
// | Author: Devil
// +----------------------------------------------------------------------
namespace app\service;

use think\facade\Db;
use app\service\SystemService;
use app\service\ResourcesService;

/**
 * APP用户中心导航服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class AppCenterNavService
{
    /**
     * 列表数据处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-08-01
     * @desc    description
     * @param   [array]          $data   [数据列表]
     * @param   [array]          $params [输入参数]
     */
    public static function AppCenterNavListHandle($data, $params = [])
    {
        if(!empty($data))
        {
            $common_platform_type = MyLang('common_platform_type');
            $common_app_event_type = MyLang('common_app_event_type');
            foreach($data as &$v)
            {
                // 平台类型
                if(isset($v['platform']))
                {
                    $v['platform_text'] = $common_platform_type[$v['platform']]['name'];
                }

                // 事件类型
                if(isset($v['event_type']) && $v['event_type'] != -1)
                {
                    $v['event_type_text'] = $common_app_event_type[$v['event_type']]['name'];
                }

                // 图片地址
                if(isset($v['images_url']))
                {
                    $v['images_url'] = ResourcesService::AttachmentPathViewHandle($v['images_url']);
                }

                // 时间
                if(isset($v['add_time']))
                {
                    $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
                }
                if(isset($v['upd_time']))
                {
                    $v['upd_time'] = empty($v['upd_time']) ? '' : date('Y-m-d H:i:s', $v['upd_time']);
                }
            }
        }
        return $data;
    }

    /**
     * 用户中心导航数据保存
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-19
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function AppCenterNavSave($params = [])
    {
        // 请求类型
        $p = [
            [
                'checked_type'      => 'length',
                'key_name'          => 'name',
                'checked_data'      => '2,60',
                'error_msg'         => '名称长度 2~60 个字符',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'platform',
                'checked_data'      => array_column(MyLang('common_platform_type'), 'value'),
                'error_msg'         => '平台类型有误',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'event_type',
                'checked_data'      => array_column(MyLang('common_app_event_type'), 'value'),
                'is_checked'        => 2,
                'error_msg'         => '事件值类型有误',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'event_value',
                'checked_data'      => '255',
                'error_msg'         => '事件值最多 255 个字符',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'images_url',
                'checked_data'      => '255',
                'error_msg'         => '请上传图片',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'desc',
                'checked_data'      => '18',
                'error_msg'         => '描述最多 18 个字符',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'sort',
                'checked_data'      => '3',
                'error_msg'         => '顺序 0~255 之间的数值',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 附件
        $data_fields = ['images_url'];
        $attachment = ResourcesService::AttachmentParams($params, $data_fields);

        // 数据
        $data = [
            'name'          => $params['name'],
            'platform'      => $params['platform'],
            'event_type'    => (isset($params['event_type']) && $params['event_type'] != '') ? intval($params['event_type']) : -1,
            'event_value'   => $params['event_value'],
            'images_url'    => $attachment['data']['images_url'],
            'desc'          => empty($params['desc']) ? '' : $params['desc'],
            'sort'          => intval($params['sort']),
            'is_enable'     => isset($params['is_enable']) ? intval($params['is_enable']) : 0,
        ];

        if(empty($params['id']))
        {
            $data['add_time'] = time();
            if(Db::name('AppCenterNav')->insertGetId($data) > 0)
            {
                return DataReturn(MyLang('insert_success'), 0);
            }
            return DataReturn(MyLang('insert_fail'), -100);
        } else {
            $data['upd_time'] = time();
            if(Db::name('AppCenterNav')->where(['id'=>intval($params['id'])])->update($data))
            {
                return DataReturn(MyLang('edit_success'), 0);
            }
            return DataReturn(MyLang('edit_fail'), -100); 
        }
    }

    /**
     * 用户中心导航删除
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function AppCenterNavDelete($params = [])
    {
        // 参数是否有误
        if(empty($params['ids']))
        {
            return DataReturn('操作id有误', -1);
        }
        // 是否数组
        if(!is_array($params['ids']))
        {
            $params['ids'] = explode(',', $params['ids']);
        }

        // 删除操作
        if(Db::name('AppCenterNav')->where(['id'=>$params['ids']])->delete())
        {
            return DataReturn(MyLang('delete_success'), 0);
        }

        return DataReturn(MyLang('delete_fail'), -100);
    }

    /**
     * 用户中心导航状态更新
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     * @param    [array]          $params [输入参数]
     */
    public static function AppCenterNavStatusUpdate($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => '操作id有误',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'field',
                'error_msg'         => '操作字段有误',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'state',
                'checked_data'      => [0,1],
                'error_msg'         => '状态有误',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 数据更新
        if(Db::name('AppCenterNav')->where(['id'=>intval($params['id'])])->update([$params['field']=>intval($params['state']), 'upd_time'=>time()]))
        {
           return DataReturn(MyLang('operate_success'), 0);
        }
        return DataReturn(MyLang('operate_fail'), -100);
    }

    /**
     * APP获取用户中心导航
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-11-19
     * @desc    description
     * @param   array           $params [description]
     */
    public static function AppCenterNav($params = [])
    {
        // 缓存
        $key = SystemService::CacheKey('shopxo.cache_app_user_center_navigation_key').APPLICATION_CLIENT_TYPE;
        $data = MyCache($key);

        if(empty($data))
        {
            $field = 'id,name,images_url,event_value,event_type,desc';
            $order_by = 'sort asc,id asc';
            $data = Db::name('AppCenterNav')->field($field)->where(['platform'=>APPLICATION_CLIENT_TYPE, 'is_enable'=>1])->order($order_by)->select()->toArray();
            if(!empty($data))
            {
                foreach($data as &$v)
                {
                    $v['images_url'] = ResourcesService::AttachmentPathViewHandle($v['images_url']);
                    $v['event_value'] = empty($v['event_value']) ? null : htmlspecialchars_decode($v['event_value']);
                }
            }
            // 存储缓存
            MyCache($key, $data, 60);
        }

        // 手机用户中心导航钩子
        $hook_name = 'plugins_service_app_user_center_navigation_'.APPLICATION_CLIENT_TYPE;
        MyEventTrigger($hook_name, [
            'hook_name'     => $hook_name,
            'is_backend'    => true,
            'data'          => &$data,
        ]);

        return $data;
    }
}
?>