<?php

namespace App\Admin\Actions;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class Status extends RowAction
{
    public $name = 'Status';

    public function __construct($status = null)
    {
        if ($status !== null) {
            $this->name = $status ? 'Disable' : 'Enable';
        }

        parent::__construct();
    }

    public function handle(Model $model)
    {
        $model->status = !$model->status;
        $model->save();

        return $this->response()->success('Status has been changed.')->refresh();
    }

}
