<?php

namespace tourze\Session\Adapter;

use tourze\Base\Helper\Cookie;
use tourze\Http\Http;
use tourze\Session\SessionAdapter;

/**
 * Native PHP session class.
 *
 * @package    Base
 * @category   Session
 * @author     YwiSax
 */
class NativeAdapter extends SessionAdapter
{

    /**
     * @return  string
     */
    public function id()
    {
        return session_id();
    }

    /**
     * @param   string $id session id
     * @return  null
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
            // Set the session id
            session_id($id);
        }

        // Start the session
        Http::sessionStart();

        // Use the $_SESSION global for storing data
        $this->_data =& $_SESSION;

        return null;
    }

    /**
     * @return  string
     */
    protected function _regenerate()
    {
        // Regenerate the session id
        session_regenerate_id();

        return session_id();
    }

    /**
     * @return bool
     */
    protected function _write()
    {
        Http::sessionWriteClose();
        return true;
    }

    /**
     * 重启会话
     *
     * @return  bool
     */
    protected function _restart()
    {
        $status = Http::sessionStart();
        // 保存当前会话数据
        $this->_data =& $_SESSION;
        return $status;
    }

    /**
     * @return  bool
     */
    protected function _destroy()
    {
        // Destroy the current session
        session_destroy();

        // Did destruction work?
        $status = ! session_id();

        if ($status)
        {
            // Make sure the session cannot be restarted
            Cookie::delete($this->_name);
        }

        return $status;
    }

}
