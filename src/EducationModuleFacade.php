<?php

namespace Digitalcubez\EducationModule;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Digitalcubez\EducationModule\Skeleton\SkeletonClass
 */
class EducationModuleFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'education-module';
    }
}
