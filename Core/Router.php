<?php

namespace Core;
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 06.11.17
 * Time: 15:31
 */
/*
 * ROUTER
 */

class Router
{
    protected $routes = [];
    protected $params = [];


    public function add($route, $params = [])
    {
        //Convert the route to a regular expression: escape forward slashes
        $route = preg_replace('/\//', '\\/',$route);

        //Convert variables e.g. {controller}
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route);

        //Convert variables with custom regular expressions e.g. {id: \d+}
        $route = preg_replace('/\{([a-z]+):([^\}/','(?P<\1>\2)',$route);

        //Add start and end delimiters, and case insensitive flag
        $route = '/^' . $route . '$/i';


        $this->routes[$route] = $params;
    }


    public function getRoutes()
    {
        return $this->routes;
    }

    /*
     * Match the route to the routes in the routing table, setting the $params
     * property if a route is found
     */
    public function match($url)
    {
        foreach ($this->routes as $route => $params) {
          /*  if ($url == $route) {
                $this->params = $params;
                return true;
            }
        }*/


        //Match to the fixed URL format /controller/action
        //$reg_exp = "/^(?P<controller>[a-z-]+\/(?P<action>[a-z-]+)$/";

        if (preg_match($route, $url, $matches)) {
            // Get named capture group values
            //$params = [];

            foreach ($matches as $key => $match) {
                if (is_string($key)) {
                    $params[$key] = $match;
                }
            }
            $this->params = $params;
            return true;
        }
        }
        return false;
    }

    /*
     * Get the currently matched parameters
     */
    public function getParams()
    {
        return $this->params;
    }

    public function dispatch($url)
    {
        $url = $this->removeQueryStringVariables($url);

        if ($this->match($url)) {
            $controller = $this->params['controller'];
            $controller = $this->convertToStudlyCaps($controller);
            $controller = "app\Controllers\\controller";

            if (class_exists($controller)) {
                $controller_object = new $controller();

                $action = $this->params['action'];
                $action = $this->convertToCamelCase($action);

                if (is_callable([$controller_object, $action])) {
                    $controller_object->$action();
                } else {
                    echo "Method $action (in controller $controller) not found";
                }
            } else {
                echo "Controller class $controller not found";
            }
        } else {
            echo 'No route matched.';
        }
    }

    protected function convertToStudlyCaps($string)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    protected function convertToCamelCase($string)
    {
        return lcfirst($this->convertToStudlyCaps($string));
    }

    protected function removeQueryStringVariables($url)
    {
        if ($url != '') {
            $parts = explode('&', $url, 2);

            if (strpos($parts[0], '=') === false) {
                $url = $parts[0];
            } else {
                $url = '';
            }
        }
        return $url;
    }
}