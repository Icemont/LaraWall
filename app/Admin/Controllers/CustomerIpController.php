<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Status;
use App\Models\Customer;
use App\Models\CustomerIp;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\MessageBag;

class CustomerIpController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Customer IPs';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CustomerIp());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('customer_id', __('Customer'))->display(function () {
            return '<a href="' . route('admin.customers.show', ['customer' => $this->customer_id]) . '">' . $this->customer->name . '</a>';
        });
        $grid->column('ip', __('IP'))->copyable();
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
            $filter->equal('customer_id', 'Customer')->select(Customer::all()->pluck('name', 'id'));
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
        $customer = CustomerIp::findOrFail($id);
        return redirect()->route('admin.customers.show', ['customer' => $customer->customer_id]);
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CustomerIp());
        $form->tools(function (Form\Tools $tools) {
            $tools->disableList();
            $tools->disableView();
            $tools->disableDelete();
        });

        if ($form->isEditing()) {
            $form->ignore('customer_id');
            $form->text('customer.name', 'Customer')->readonly();
            $form->divider('Editing Customer IP');
        } else {

            $form->select('customer_id', 'Customer')->options(Customer::all()->pluck('name', 'id'))->required()->default(intval(request('customer_id')));
            $form->divider('Create Customer IP');
        }
        $form->ip('ip', __('IP'))->required();
        $form->textarea('note', __('Note'));

        $form->footer(function ($footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        $form->saving(function ($form) {
            if (CustomerIp::where('customer_id', '=', $form->customer_id)->where('ip', '=', $form->ip)->first()) {
                $error = new MessageBag([
                    'title' => 'Error',
                    'message' => 'This customer already has that IP!',
                ]);

                return back()->with(compact('error'));
            }
        });


        $form->saved(function ($form) {

            $success = new MessageBag([
                'title' => 'Saved',
                'message' => 'Customer IP &quot;' . $form->model()->ip . '&quot; with ID #' . $form->model()->id . ' saved!',
            ]);

            return redirect()
                ->route('admin.customers.show', ['customer' => $form->model()->customer_id])
                ->with(compact('success'));
        });

        return $form;
    }
}
