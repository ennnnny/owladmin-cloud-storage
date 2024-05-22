<?php

namespace Slowlyo\CloudStorage\Http\Controllers;

use Slowlyo\OwlAdmin\Controllers\AdminController;
use Slowlyo\OwlAdmin\Renderers\Form;
use Slowlyo\CloudStorage\Traits;

class BaseController extends AdminController
{
    use Traits\CloudStorageQueryPathTrait;

    public function detail(): Form
    {
        return $this->baseDetail()->body([]);
    }
}
