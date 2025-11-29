<?php

namespace App\Infrastructure\Container;

use ReflectionClass;
use ReflectionParameter;

class ContainerHelper
{
    public static function resolve(\Pimple\Container $container, string $className)
    {
        $reflection = new ReflectionClass($className);
        
        if (!$reflection->hasMethod('__construct')) {
            return new $className();
        }
        
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();
        
        if (empty($parameters)) {
            return new $className();
        }
        
        $dependencies = [];
        foreach ($parameters as $parameter) {
            $dependencies[] = self::resolveParameter($container, $parameter);
        }
        
        return $reflection->newInstanceArgs($dependencies);
    }
    
    private static function resolveParameter(\Pimple\Container $container, ReflectionParameter $parameter)
    {
        $type = $parameter->getType();
        
        if (!$type) {
            throw new \RuntimeException(
                "Não é possível resolver automaticamente o parâmetro '{$parameter->getName()}' " .
                "da classe '{$parameter->getDeclaringClass()->getName()}' (sem type hint)"
            );
        }
        
        if (method_exists($type, 'getName')) {
            $typeName = $type->getName();
            $isBuiltin = $type->isBuiltin();
        } else {
            $typeName = (string) $type;
            $isBuiltin = in_array($typeName, ['int', 'float', 'string', 'bool', 'array', 'callable', 'iterable']);
        }
        
        if ($isBuiltin) {
            throw new \RuntimeException(
                "Não é possível resolver automaticamente o parâmetro '{$parameter->getName()}' " .
                "da classe '{$parameter->getDeclaringClass()->getName()}' (tipo builtin: {$typeName})"
            );
        }
        
        if ($container->offsetExists($typeName)) {
            return $container[$typeName];
        }
        
        return self::resolve($container, $typeName);
    }
    
    public static function register(\Pimple\Container $container, string $className, $alias = null)
    {
        $key = $alias ?? $className;
        
        $container[$key] = function ($c) use ($className) {
            return self::resolve($c, $className);
        };
    }
    
    public static function registerMany(\Pimple\Container $container, array $classes)
    {
        foreach ($classes as $key => $className) {
            if (is_numeric($key)) {
                self::register($container, $className);
            } else {
                self::register($container, $className, $key);
            }
        }
    }
}

