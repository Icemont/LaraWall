<?php

namespace App\Admin\Controllers;

use App\Admin\Dashboard\Info;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->title('LaraWall Admin')
            ->description('Web panel to manage access to service ports of a server group')
            ->row('<div class="h3 text-center">LaraWall Dashboard</div>')
            ->row(function (Row $row) {
                $row->column(4, function (Column $column) {
                    $column->append(Info::statistics());
                });

                $row->column(4, function (Column $column) {
                    $column->append(Dashboard::environment());
                });
            });
    }
}
