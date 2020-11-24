<?php

declare(strict_types=1);

namespace Chocofamily\Tarantool\Traits;

trait Dsn
{
    /**
     * Create a DSN string from a configuration.
     *
     * @param  array $config
     * @return string
     */
    protected function getDsn(array $config)
    {
        return $this->hasDsnString($config)
            ? $this->getDsnString($config)
            : $this->getHostDsn($config);
    }

    /**
     * Determine if the given configuration array has a dsn string.
     *
     * @param  array  $config
     * @return bool
     */
    protected function hasDsnString(array $config)
    {
        return isset($config['dsn']) && ! empty($config['dsn']);
    }

    /**
     * Get the DSN string form configuration.
     *
     * @param  array  $config
     * @return string
     */
    protected function getDsnString(array $config)
    {
        return $config['dsn'];
    }

    /**
     * Get the DSN string for a host / port configuration.
     *
     * @param  array  $config
     * @return string
     */
    protected function getHostDsn(array $config)
    {
        $host = $config['host'];

        if (strpos($host, ':') === false && ! empty($config['port'])) {
            $host = $host.':'.$config['port'];
        }

        $auth = $config['username'].':'.$config['password'];

        $options = isset($config['options']) && ! empty($config['options']) ? http_build_query($config['options'], null, '&') : null;

        $optionConnectType = isset($config['type']) && ! empty($config['type']);
        $connType = (($optionConnectType || ($optionConnectType && in_array($config['type'], ['tcp', 'unix']))) ? $config['type'] : 'tcp');

        return $connType.'://'.$auth.'@'.$host.($options ? '/?'.$options : '');
    }
}
