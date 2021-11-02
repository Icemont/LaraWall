<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Status;
use App\Models\Package;
use App\Models\Service;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;

class PackageController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Packages';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Package());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('name', __('Name'))->link(function ($model) {
            return route('admin.packages.show', ['package' => $model->id]);
        }, '');
        $grid->services(__('Package Services'))
            ->display(function ($services) {
                return count($services);
            })
            ->modal('Package Services', function ($model) {

                $pkgs = $model->services->map(function ($pkg) {
                    $pkg = $pkg->only(['id', 'name', 'port', 'server']);
                    $pkg['server'] = $pkg['server']->name;
                    return $pkg;
                });

                return new Table(['ID', 'Service name', 'Service port', 'Server'], $pkgs->toArray());
            });

        $grid->column('status', __('Status'))->bool();
        $grid->column('created_at', __('Created'))->display(function ($date) {
            return date('d.m.Y', strtotime($date));
        });
        $grid->column('updated_at', __('Last updated'))->display(function ($date) {
            return date('d.m.Y', strtotime($date));
        });

        $grid->actions(function ($actions) {
            $actions->add(new Status($actions->row->status));
        });

        $grid->filter(function($filter){
            $filter->like('name', 'Name');
            $filter->equal('status')->radio([
                '' => 'All',
                1 => 'Active',
                0 => 'Inactive',
            ]);
        });

        $grid->quickSearch('name', 'note')->placeholder('Name or Note');

        $grid->export(function ($export) {
            $export->filename('packages.csv');
            $export->only(['id', 'name', 'status', 'created_at']);
            $export->originalValue(['id', 'name', 'status']);
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Package::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('status', __('Status'))->using([0 => 'Inactive', 1 => 'Active']);
        $show->field('note', __('Note'));
        $show->field('created_at', __('Created'));
        $show->field('updated_at', __('Last updated'));

        $show->services('List of Services in Package #' . $id, function ($service) {

            $service->resource('/admin/services');

            $service->disableCreateButton();
            $service->disablePagination();
            $service->disableFilter();
            $service->disableExport();
            $service->disableRowSelector();
            $service->disableColumnSelector();
            $service->disableActions();

            $service->id('ID');
            $service->column('name', __('Name'));
            $service->column('port', __('Port'));
            $service->column('server_id', __('Server'))->display(function () {
                return '<a href="' . route('admin.servers.show', ['server' => $this->server_id]) . '">#' . $this->server_id . '</a>';
            });
            $service->created_at('Created')->display(function ($date) {
                return date("d.m.Y", strtotime($date));
            });
            $service->updated_at('Last updated')->display(function ($date) {
                return date("d.m.Y", strtotime($date));
            });
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Package());

        $form->text('name', __('Name'));
        $form->switch('status', __('Status'))->default(1);
        $form->multipleSelect('services')->options(Service::all()->pluck('name', 'id'));
        $form->textarea('note', __('Note'));
        return $form;
    }
}
