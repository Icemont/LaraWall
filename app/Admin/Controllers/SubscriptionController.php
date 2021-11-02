<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Status;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Subscription;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\MessageBag;

class SubscriptionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Subscriptions';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Subscription());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('internal_name', __('Internal name'))->link(function ($model) {
            return route('admin.subscriptions.show', ['subscription' => $model->id]);
        }, '');
        $grid->column('package_id', __('Package ID'))->link(function ($model) {
            return route('admin.packages.show', ['package' => $model->package->id]);
        }, '');
        $grid->column('package.name', __('Package name'))->link(function ($model) {
            return route('admin.packages.show', ['package' => $model->package->id]);
        }, '');
        $grid->column('customer_id', __('Customer ID'))->link(function ($model) {
            return route('admin.customers.show', ['customer' => $model->customer->id]);
        }, '');
        $grid->column('customer.name', __('Customer name'))->link(function ($model) {
            return route('admin.customers.show', ['customer' => $model->customer->id]);
        }, '');
        $grid->column('package.status', __('Package status'))->bool();
        $grid->column('status', __('Subscription status'))->bool();
        $grid->expiry(__('Expiry date'));

        $grid->column('note', __('Note'))->limit(25)->default('&mdash;');

        $grid->actions(function ($actions) {
            $actions->add(new Status($actions->row->status));
        });

        $grid->filter(function ($filter) {
            $filter->like('internal_name', __('Internal name'));
            $filter->equal('package_id', __('Package'))->select(Package::all()->pluck('name', 'id'));
            $filter->equal('customer_id', __('Customer'))->select(Customer::all()->pluck('name', 'id'));
            $filter->equal('status')->radio([
                '' => 'All',
                1 => 'Active',
                0 => 'Inactive',
            ]);
        });

        $grid->quickSearch('internal_name', 'note')->placeholder('Internal name or Note');

        $grid->export(function ($export) {
            $export->filename('subscriptions.csv');
            $export->only(['id', 'internal_name', 'customer_id', 'customer.name', 'package_id', 'package.name', 'status', 'expiry', 'created_at']);
            $export->originalValue(['id', 'internal_name', 'customer_id', 'customer.name', 'package_id', 'package.name', 'status']);
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
        $show = new Show(Subscription::findOrFail($id));

        $show->panel()
            ->tools(function ($tools) {
                //$tools->disableList();
                $tools->disableDelete();
            });

        $show->field('id', __('Id'));
        $show->field('internal_name', __('Internal name'));
        $show->field('customer_id', __('Customer ID'));
        $show->field('customer.name', __('Customer name'));
        $show->field('package_id', __('Package ID'));
        $show->field('package.name', __('Package name'));
        $show->field('customer_data', __('Customer data'));
        $show->field('status', __('Status'))->using([0 => 'Inactive', 1 => 'Active']);
        $show->field('expiry', __('Expiry'));
        $show->field('note', __('Note'));
        $show->field('created_at', __('Created'));
        $show->field('updated_at', __('Last updated'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Subscription());

        $form->tools(function (Form\Tools $tools) {
            $tools->disableList();
            $tools->disableDelete();
        });

        if ($form->isEditing()) {
            $form->ignore('customer_id');
            $form->ignore('package_id');

            $form->text('customer.name', 'Customer')->readonly();
            $form->text('package.name', 'Package')->readonly();
            $form->divider('Edit Subscription');
        } else {
            $form->select('customer_id', 'Customer')
                ->options(Customer::all()->pluck('name', 'id'))
                ->required()
                ->default(intval(request('customer_id')));
            $form->divider('Create Subscription');
            $form->select('package_id', 'Package')->options(Package::all()->pluck('name', 'id'))->required();
        }
        $form->text('internal_name', __('Internal name'));
        $form->switch('status', __('Status'))->default(1);
        $form->datetime('expiry', __('Expiry'))->default(date('Y-m-d H:i:s'));
        $form->textarea('note', __('Note'));
        $form->textarea('customer_data', __('Customer data'));

        $form->saving(function ($form) {
            if (Subscription::where('customer_id', '=', $form->customer_id)->where('package_id', '=', $form->package_id)->first()) {
                $error = new MessageBag([
                    'title' => 'Error',
                    'message' => 'This customer already has that package!',
                ]);

                return back()->with(compact('error'));
            }
        });

        $form->saved(function ($form) {
            $success = new MessageBag([
                'title' => 'Saved',
                'message' => 'Subscription with ID #' . $form->model()->id . ' saved!',
            ]);

            return redirect()
                ->route('admin.customers.show', ['customer' => $form->model()->customer_id])
                ->with(compact('success'));
        });

        return $form;
    }
}
