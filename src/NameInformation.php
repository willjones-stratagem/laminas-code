<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code;

class NameInformation
{
    /**
     * @var string
     */
    protected $namespace = null;

    /**
     * @var array
     */
    protected $uses = array();

    /**
     * @param  string $namespace
     * @param  array $uses
     */
    public function __construct($namespace = null, array $uses = array())
    {
        if ($namespace) {
            $this->setNamespace($namespace);
        }
        if ($uses) {
            $this->setUses($uses);
        }
    }

    /**
     * @param  string $namespace
     * @return NameInformation
     */
    public function setNamespace($namespace)
    {
        $this->namespace = (string) $namespace;
        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return bool
     */
    public function hasNamespace()
    {
        return ($this->namespace !== null);
    }

    /**
     * @param  array $uses
     * @return NameInformation
     */
    public function setUses(array $uses)
    {
        $this->uses = array();
        $this->addUses($uses);

        return $this;
    }

    /**
     * @param  array $uses
     * @return NameInformation
     */
    public function addUses(array $uses)
    {
        foreach ($uses as $use => $as) {
            if (is_int($use)) {
                $this->addUse($as);
            } elseif (is_string($use)) {
                $this->addUse($use, $as);
            }
        }

        return $this;
    }

    /**
     * @param  array|string $use
     * @param  string $as
     */
    public function addUse($use, $as = null)
    {
        if (is_array($use) && array_key_exists('use', $use) && array_key_exists('as', $use)) {
            $uses = $use;
            $use  = $uses['use'];
            $as   = $uses['as'];
        }

        $use = trim($use, '\\');
        if ($as === null) {
            $as                  = trim($use, '\\');
            $nsSeparatorPosition = strrpos($as, '\\');
            if ($nsSeparatorPosition !== false && $nsSeparatorPosition !== 0 && $nsSeparatorPosition != strlen($as)) {
                $as = substr($as, $nsSeparatorPosition + 1);
            }
        }

        $this->uses[$use] = $as;
    }

    /**
     * @return array
     */
    public function getUses()
    {
        return $this->uses;
    }

    /**
     * @param  string $name
     * @return string
     */
    public function resolveName($name)
    {
        if ($this->namespace && !$this->uses && strlen($name) > 0 && $name{0} != '\\') {
            return $this->namespace . '\\' . $name;
        }

        if (!$this->uses || strlen($name) <= 0 || $name{0} == '\\') {
            return ltrim($name, '\\');
        }

        if ($this->namespace || $this->uses) {
            $firstPart = $name;
            if (($firstPartEnd = strpos($firstPart, '\\')) !== false) {
                $firstPart = substr($firstPart, 0, $firstPartEnd);
            } else {
                $firstPartEnd = strlen($firstPart);
            }
            if (($fqns = array_search($firstPart, $this->uses)) !== false) {
                return substr_replace($name, $fqns, 0, $firstPartEnd);
            }
            if ($this->namespace) {
                return $this->namespace . '\\' . $name;
            }
        }

        return $name;
    }
}
