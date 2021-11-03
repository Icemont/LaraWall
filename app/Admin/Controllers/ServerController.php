<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Status;
use App\Models\Server;
use App\Rules\Domain;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use PragmaRX\Countries\Package\Countries;

class ServerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Servers';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Server());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('name', __('Name'))->link(function ($model) {
            return route('admin.servers.show', ['server' => $model->id]);
        }, '');
        $grid->column('ip', __('IP address'))->copyable();
        $grid->column('hostname', __('Hostname'))->default('&mdash;');
        $grid->services(__('Service ports'))
            ->display(function ($services) {
                return count($services);
            })
            ->modal('Service ports', function ($model) {

                $comments = $model->services->map(function ($comment) {
                    return $comment->only(['id', 'name', 'port']);
                });

                return new Table(['ID', 'Name', 'Port'], $comments->toArray());
            });
        $grid->column('status', __('Status'))->bool();
        $grid->column('country', __('Country'))->default('&mdash;')->searchable();
        $grid->column('isp', __('DC / ISP'))->default('&mdash;')->searchable();
        $grid->column('created_at', __('Created'))->display(function ($date) {
            return date('d.m.Y', strtotime($date));
        });

        $grid->actions(function ($actions) {
            $actions->add(new Status($actions->row->status));
        });

        $grid->export(function ($export) {
            $export->filename('Servers.csv');
            $export->only(['id', 'name', 'hostname', 'ip', 'status', 'isp', 'created_at']);
            $export->originalValue(['id', 'name', 'hostname', 'ip', 'status']);
        });

        $grid->filter(function($filter){
            $filter->like('name', 'Name');
            $filter->like('hostname', 'Hostname');
            $filter->like('ip', 'IP address');
            $filter->like('country', 'Country')->select((new Countries())->all()->pluck('name.common', 'name.common')->toArray());
            $filter->like('isp', 'DC / ISP');
            $filter->equal('status')->radio([
                '' => 'All',
                1 => 'Active',
                0 => 'Inactive',
            ]);
        });

        $grid->quickSearch('name', 'hostname', 'ip', 'country', 'note')->placeholder('Name, Hostname, IP, Country or Note');

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
        $show = new Show(Server::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('name', __('Name'));
        $show->field('hostname', __('Hostname'));
        $show->field('ip', __('IP address'));
        $show->field('isp', __('DC / ISP'));
        $show->field('country', __('Country'));
        $show->field('city', __('City'));
        $show->field('status', __('Status'))->using([0 => 'Inactive', 1 => 'Active']);
        $show->field('attributes', __('Server attributes'))->unescape()->as(function ($attributes) {
            if (is_array($attributes)) {
                return new Table(['Key', 'Value'], $attributes);
            }
            return false;
        });
        $show->field('note', __('Note'))->unescape()->as(function ($note) {
            return str_replace("\n", '<br />', htmlspecialchars($note));
        });
        $show->field('created_at', __('Created'));
        $show->field('updated_at', __('Last updated'));

        $show->services('Service Ports of Server #' . $id, function ($ports) {

            $ports->resource('/admin/services');

            $ports->disableColumnSelector();
            $ports->disablePagination();

            $ports->actions(function ($actions) {
                $actions->disableView();
                $actions->add(new Status($actions->row->status));
            });

            $ports->id('ID');
            $ports->name('Service name');
            $ports->port('Service port');
            $ports->column('status', __('Status'))->bool();
            $ports->created_at('Created')->display(function ($date) {
                return date("d.m.Y", strtotime($date));
            });
            $ports->updated_at('Last updated')->display(function ($date) {
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
        $form = new Form(new Server());

        $form->tab('Basic data', function ($form) {
            $form->text('name', __('Name'))->required();
            $form->text('hostname', __('Hostname'))->icon('fa-internet-explorer');
            $form->ip('ip', __('IP address'))
                ->required()
                ->creationRules(['required', "unique:servers"])
                ->updateRules(['required', "unique:servers,ip,{{id}}"]);

            $states = [
                'on' => ['value' => 1, 'text' => 'enable', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => 'disable', 'color' => 'danger'],
            ];
            $form->switch('status', __('Status'))->states($states)->default(1);

            $form->textarea('note', __('Note'));

        })->tab('Location', function ($form) {
            $form->text('isp', __('DC / ISP'))->placeholder('Input the name of the hosting provider, data center, etc.');
            $countries = new Countries();
            $form->select('country', 'Country')->options($countries->all()->pluck('name.common', 'name.common')->toArray());
            $form->text('city', __('City'));

        })->tab('Server attributes', function ($form) {
            $form->keyValue('attributes', __('Attributes'))->fill(['attributes' => ['Login' => '']]);
        });

        $form->saving(function ($form) {

            if (!Validator::make(['hostname' => $form->hostname], ['hostname' => new Domain()])->passes()) {
                $error = new MessageBag([
                    'title' => 'Error!',
                    'message' => 'The Hostname must be a valid domain or hostname without an http protocol e.g. google.com',
                ]);

                return back()->with(compact('error'));
            }
        });

        return $form;
    }
}
