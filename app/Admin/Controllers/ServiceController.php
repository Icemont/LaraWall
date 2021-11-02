<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Status;
use App\Models\Server;
use App\Models\Service;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\MessageBag;

class ServiceController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Service Ports';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Service());
        $grid->column('id', __('ID'))->sortable();
        $grid->column('name', __('Name'));
        $grid->column('server_id', __('Server'))->display(function () {
            return '<a href="' . route('admin.servers.show', ['server' => $this->server_id]) . '">' . $this->server->name . '</a>';
        });
        $grid->column('port', __('Port'));
        $grid->column('status', __('Status'))->bool();
        $grid->column('created_at', __('Created'))->display(function ($date) {
            return date("d.m.Y", strtotime($date));
        });
        $grid->column('updated_at', __('Last updated'))->display(function ($date) {
            return date("d.m.Y", strtotime($date));
        });

        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->add(new Status($actions->row->status));
        });

        $grid->filter(function($filter){
            $filter->like('name', 'Name');
            $filter->equal('port', 'Port')->integer();
            $filter->equal('server_id', 'Server')->select(Server::all()->pluck('name', 'id'));
            $filter->equal('status')->radio([
                '' => 'All',
                1 => 'Active',
                0 => 'Inactive',
            ]);
        });

        $grid->disableExport();

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show|\Illuminate\Http\RedirectResponse
     */
    public function show($id, Content $content)
    {
        $service = Service::findOrFail($id);
        return redirect()->route('admin.servers.show', ['server' => $service->server_id]);
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Service());
        $form->tools(function (Form\Tools $tools) {
            $tools->disableList();
            $tools->disableView();
            $tools->disableDelete();
        });

        if ($form->isEditing()) {
            $form->ignore('server_id');
            $form->text('server.name', 'Server')->readonly();
            $form->divider('Editing Service Port');
        } else {

            $form->select('server_id', 'Server')->options(Server::all()->pluck('name', 'id'))->required()->default(intval(request('server_id')));
            $form->divider('Create Service Port');
        }
        $form->text('name', __('Name'))->required();
        $form->switch('status', __('Status'))->default(1);
        $form->number('port', __('Service Port'))
            ->rules('required|numeric|between:1,65535')
            ->default(1234);

        $form->footer(function ($footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        $form->saving(function ($form) {
            if (Service::where('server_id', '=', $form->server_id)->where('port', '=', $form->port)->first()) {
                $error = new MessageBag([
                    'title' => 'Error',
                    'message' => 'This server already has a service with this port!',
                ]);

                return back()->with(compact('error'));
            }
        });

        $form->saved(function ($form) {

            $success = new MessageBag([
                'title' => 'Saved',
                'message' => 'Service Port ' . $form->model()->port . ' with ID #' . $form->model()->id . ' saved!',
            ]);

            return redirect()
                ->route('admin.servers.show', ['server' => $form->model()->server_id])
                ->with(compact('success'));
        });

        return $form;
    }
}
