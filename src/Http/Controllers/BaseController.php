<?php

namespace Ennnnny\CloudStorage\Http\Controllers;

use Ennnnny\CloudStorage\Traits;
use Slowlyo\OwlAdmin\Controllers\AdminController;
use Slowlyo\OwlAdmin\Renderers\Form;

class BaseController extends AdminController
{
    use Traits\CloudStorageQueryPathTrait;

    public function detail(): Form
    {
        return $this->baseDetail()->body([]);
    }
}
