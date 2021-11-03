<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Status;
use App\Models\Customer;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use PragmaRX\Countries\Package\Countries;

class CustomerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Customers';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Customer());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('name', __('Name'))->link(function ($model) {
            return route('admin.customers.show', ['customer' => $model->id]);
        }, '');
        $grid->column('nickname', __('Nickname'))->default('&mdash;');
        $grid->column('email', __('E-mail'))->default('&mdash;');

        $grid->subscriptions(__('Subscriptions'))
            ->display(function ($services) {
                return count($services);
            })->modal('Customer Subscriptions', function ($model) {

                $subscriptions = $model->subscriptions->map(function ($subscription) {
                    $subscription = $subscription->only(['id', 'package', 'expiry']);
                    $subscription['package'] = $subscription['package']->name;
                    return $subscription;
                });

                return new Table(['ID', 'Package', 'Expiry date'], $subscriptions->toArray());
            });

        $grid->ips(__('Customer IPs'))
            ->display(function ($services) {
                return count($services);
            })
            ->modal('Customer IPs', function ($model) {

                $ips = $model->ips->map(function ($ip) {
                    return $ip->only(['id', 'ip']);
                });

                return new Table(['ID', 'IP'], $ips->toArray());
            });

        $grid->column('legal', __('Account type'))->using([0 => 'Personal', 1 => 'Business']);
        $grid->column('country', __('Country'))->default('&mdash;')->searchable();
        $grid->column('status', __('Status'))->bool();
        $grid->column('created_at', __('Created'))->display(function ($date) {
            return date('d.m.Y', strtotime($date));
        });
        $grid->column('updated_at', __('Last updated'))->display(function ($date) {
            return date('d.m.Y', strtotime($date));
        });

        $grid->export(function ($export) {
            $export->filename('customers.csv');
            $export->only(['id', 'name', 'nickname', 'legal', 'status', 'created_at']);
            $export->originalValue(['id', 'name', 'email', 'status']);
        });

        $grid->filter(function ($filter) {
            $filter->like('name', 'Name');
            $filter->like('nickname', 'Nickname');
            $filter->like('email', 'E-mail');
            $filter->like('ips.ip', 'IP');
            $filter->like('country', 'Country')->select((new Countries())->all()->pluck('name.common', 'name.common')->toArray());
            $filter->equal('status')->radio([
                '' => 'All',
                1 => 'Active',
                0 => 'Inactive',
            ]);
            $filter->equal('legal', 'Account type')->radio([
                '' => 'All',
                0 => 'Personal',
                1 => 'Business',
            ]);
        });

        $grid->actions(function ($actions) {
            $actions->add(new Status($actions->row->status));
        });

        $grid->quickSearch('name', 'nickname', 'email', 'note')->placeholder('Name, Nickname, E-mail or Note');

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
        $show = new Show(Customer::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('name', __('Name'));
        $show->field('email', __('E-mail'));
        $show->field('legal', __('Account type'))->using([0 => 'Personal', 1 => 'Business']);
        if ($show->getModel()->legal) {
            $show->field('company', __('Company'));
        }
        $show->field('status', __('Status'))->using([0 => 'Inactive', 1 => 'Active']);
        $show->field('country', __('Country'));
        $show->field('attributes', __('Customer attributes'))->unescape()->as(function ($attributes) {
            if (is_array($attributes)) {
                return new Table(['Key', 'Value'], $attributes);
            }
            return false;
        });
        $show->field('note', __('Note'));
        $show->field('created_at', __('Created'));
        $show->field('updated_at', __('Last updated'));

        $show->ips('Customer IP addresses', function ($ips) {

            $ips->resource('/admin/customer-ips');

            $ips->disableColumnSelector();
            $ips->disablePagination();

            $ips->actions(function ($actions) {
                $actions->disableView();
                $actions->add(new Status($actions->row->status));
            });

            $ips->id('ID');
            $ips->ip('IP')->copyable();
            $ips->column('status', __('Status'))->bool();
            $ips->column('note', __('Note'))->limit(25);
            $ips->created_at('Created')->display(function ($date) {
                return date("d.m.Y", strtotime($date));
            });
            $ips->updated_at('Last updated')->display(function ($date) {
                return date("d.m.Y", strtotime($date));
            });
        });

        $show->subscriptions('Customer Subscriptions', function ($subscriptions) {
            $subscriptions->resource('/admin/subscriptions');
            $subscriptions->disablePagination();
            $subscriptions->disableFilter();
            $subscriptions->disableExport();
            $subscriptions->disableColumnSelector();

            $subscriptions->id('ID');
            $subscriptions->column('internal_name', __('Internal name'))->default('&mdash;');
            $subscriptions->column('package.id', __('Package ID'))->link(function ($model) {
                return route('admin.packages.show', ['package' => $model->package->id]);
            }, '');
            $subscriptions->column('package.name', __('Package name'))->link(function ($model) {
                return route('admin.packages.show', ['package' => $model->package->id]);
            }, '');
            $subscriptions->column('package.status', __('Package status'))->bool();
            $subscriptions->column('status', __('Subscription status'))->bool();
            $subscriptions->expiry(__('Expiry date'));

            $subscriptions->column('note', __('Note'))->limit(25);
            $subscriptions->actions(function ($actions) {
                $actions->add(new Status($actions->row->status));
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
        $form = new Form(new Customer());

        $form->text('name', __('Name'))->required();
        $form->text('nickname', __('Nickname'))
            ->creationRules(["unique:customers"])
            ->updateRules(["unique:customers,nickname,{{id}}"])
            ->icon('fa-user-secret');
        $form->email('email', __('E-mail'))
            ->creationRules(['required', "unique:customers"])
            ->updateRules(['required', "unique:customers,email,{{id}}"]);
        $form->mobile('phone', 'Phone')->options(['mask' => '9{5,15}']);
        $form->radio('legal', 'Account type')
            ->options([
                0 => 'Personal',
                1 => 'Business',
            ])->when(1, function (Form $form) {
                $form->text('company', 'Company name');
            })->default(0);

        $countries = new Countries();
        $form->select('country', 'Country')->options($countries->all()->pluck('name.common', 'name.common')->toArray());
        $form->switch('status', __('Status'))->default(1);
        $form->keyValue('attributes', __('Attributes'))->fill(['attributes' => ['Telegram ID' => '']]);
        $form->textarea('note', __('Note'));

        return $form;
    }
}
