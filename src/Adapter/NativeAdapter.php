<?php

namespace tourze\Session\Adapter;

use tourze\Base\Base;
use tourze\Base\Helper\Cookie;
use tourze\Session\SessionAdapter;

/**
 * 使用原生的PHP session
 * 这个方法的优点是方便，缺点是无法实现分布式
 * 当然如果在php.ini中直接设置session共享，也是可以实现的
 *
 * @package tourze\Session\Adapter
 */
class NativeAdapter extends SessionAdapter
{

    /**
     * @return string
     */
    public function id()
    {
        return Base::getHttp()->sessionID();
    }

    /**
     * @param  string $id session id
     * @return null
     */
    protected function _read($id = null)
    {
        // Sync up the session cookie with CookieHelper parameters
        session_set_cookie_params($this->_lifetime, Cookie::$path, Cookie::$domain, Cookie::$secure, Cookie::$httpOnly);

        // Do not allow PHP to send Cache-Control headers
        session_cache_limiter(false);

        // Set the session cookie name
        session_name($this->_name);

        if ($id)
        {
            Base::getHttp()->sessionID($id);
        }

        Base::getHttp()->sessionStart();
        $this->_data =& $_SESSION;

        return null;
    }

    /**
     * @return string
     */
    protected function _regenerate()
    {
        Base::getHttp()->sessionRegenerateID();

        return Base::getHttp()->sessionID();
    }

    /**
     * @return bool
     */
    protected function _write()
    {
        Base::getHttp()->sessionWriteClose();
        return true;
    }

    /**
     * 重启会话
     *
     * @return bool
     */
    protected function _restart()
    {
        $status = Base::getHttp()->sessionStart();
        // 保存当前会话数据
        $this->_data =& $_SESSION;
        return $status;
    }

    /**
     * @return bool
     */
    protected function _destroy()
    {
        // Destroy the current session
        session_destroy();

        // Did destruction work?
        $status = ! Base::getHttp()->sessionID();

        if ($status)
        {
            // Make sure the session cannot be restarted
            Cookie::delete($this->_name);
        }

        return $status;
    }

}
