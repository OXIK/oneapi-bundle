<?php
/**
 * Copyright 2015 OxikStudio
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * This file is part of the OxikOneApiBundle package.
 *
 */

namespace Oxik\OneApiBundle\Services;

/**
 * The class Wrapper manage the import of infobip OneApi
 *
 * @author Manuel Raya <manuel@arrogance.es>
 */
class Wrapper
{

    /**
     * infobip namespaces.
     *
     * @var array
     */
    protected $scope = array(
        "infobip\\",
        "infobip\\models\\"
    );

    /**
     * infobip username, from symfony config.
     *
     * @var string
     */
    protected $username;

    /**
     * infobip password, from symfony config.
     *
     * @var string
     */
    protected $password;

    /**
     * Try to resolve the service, type and arguments
     *
     * @param $service string
     * @param $args array/bool
     *
     * @return $this->resolveService()
     */
    public function getService($service, $args = null)
    {
        return $this->resolveService($service, $this->scope[0], $args);
    }

    /**
     * Try to resolve the model, type and arguments
     *
     * @param $service string
     * @param $args array/bool
     *
     * @return $this->resolveService()
     */
    public function getModel($service, $args = null)
    {
        return $this->resolveService($service, $this->scope[1], $args);
    }

    /**
     * Set as class variables the config from the dependency container
     *
     * @param $config array
     *
     * @return Wrapper
     */
    public function setConfig($config)
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Check if the class exists on infobip OneApi, and returns a new instance.
     *
     * @param $service string
     * @param $scope string
     * @param $args array/bool
     *
     * @return ReflectionClass new instance
     */
    protected function resolveService($service, $scope, $args)
    {
        if (!class_exists($scope.$service)) {
            throw new \Exception("Error Processing Class: That class does not exists or can not be found.", 1);
        }
            
        $class = new \ReflectionClass($scope.$service);

        if (is_bool($args) and $args === true) {
            $args = array($this->getUsername(), $this->getPassword(), $this->getBaseUrl());
        } elseif (!is_array($args)) {
            $args = array();
        }

        return $class->newInstanceArgs($args);
    }
}
