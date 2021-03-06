<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think;

use think\config\driver\DriverInterface;
use think\config\driver\Ini;

class Config
{
    // 配置参数
    private static $config = [];
    // 参数作用域
    private static $range = '_sys_';

    // 设定配置参数的作用域
    public static function range($range)
    {
        self::$range = $range;
        if (!isset(self::$config[$range])) {
            self::$config[$range] = [];
        }
    }

    /**
     * 解析配置文件或内容
     *
     * @param string               $config 配置文件路径或内容
     * @param string               $range  作用域
     * @param DriverInterface|null $driver 配置解析驱动
     */
    public static function parse($config, $range = '', DriverInterface $driver = null)
    {
        if ($driver === null) {
            $driver = new Ini();
        }

        $range = $range ?: self::$range;
        self::set($driver->parse($config), '', $range);
    }

    // 加载配置文件
    public static function load($file, $name = '', $range = '')
    {
        $range = $range ?: self::$range;
        $file  = strpos($file, '.') ? $file : APP_PATH . $file . EXT;
        if (!isset(self::$config[$range])) {
            self::$config[$range] = [];
        }
        return is_file($file) ? self::set(include $file, $name, $range) : self::$config[$range];
    }

    // 检测配置是否存在
    public static function has($name, $range = '')
    {
        $range = $range ?: self::$range;
        $name  = strtolower($name);

        if (!strpos($name, '.')) {
            return isset(self::$config[$range][$name]);
        } else {
            // 二维数组设置和获取支持
            $name = explode('.', $name);
            return isset(self::$config[$range][$name[0]][$name[1]]);
        }
    }

    // 获取配置参数 为空则获取所有配置
    public static function get($name = null, $range = '')
    {
        $range = $range ?: self::$range;
        // 无参数时获取所有
        if (empty($name) && isset(self::$config[$range])) {
            return self::$config[$range];
        }
        $name = strtolower($name);
        if (!strpos($name, '.')) {
            // 判断环境变量
            if (isset($_ENV[ENV_PREFIX . $name])) {
                return $_ENV[ENV_PREFIX . $name];
            }
            return isset(self::$config[$range][$name]) ? self::$config[$range][$name] : null;
        } else {
            // 二维数组设置和获取支持
            $name = explode('.', $name);
            // 判断环境变量
            if (isset($_ENV[ENV_PREFIX . $name[0] . '_' . $name[1]])) {
                return $_ENV[ENV_PREFIX . $name[0] . '_' . $name[1]];
            }
            return isset(self::$config[$range][$name[0]][$name[1]]) ? self::$config[$range][$name[0]][$name[1]] : null;
        }
    }

    // 设置配置参数 name为数组则为批量设置
    public static function set($name, $value = null, $range = '')
    {
        $range = $range ?: self::$range;
        if (!isset(self::$config[$range])) {
            self::$config[$range] = [];
        }
        if (is_string($name)) {
            $name = strtolower($name);
            if (!strpos($name, '.')) {
                self::$config[$range][$name] = $value;
            } else {
                // 二维数组设置和获取支持
                $name                                     = explode('.', $name);
                self::$config[$range][$name[0]][$name[1]] = $value;
            }
            return;
        } elseif (is_array($name)) {
            // 批量设置
            if (!empty($value)) {
                return self::$config[$range][$value] = array_change_key_case($name);
            } else {
                return self::$config[$range] = array_merge(self::$config[$range], array_change_key_case($name));
            }
        } else {
            // 为空直接返回 已有配置
            return self::$config[$range];
        }
    }

    // 重置配置参数
    public static function reset($name, $value = null, $range = '')
    {
       self::$config = [];
    }
}
