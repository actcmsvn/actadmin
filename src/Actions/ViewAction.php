<?php

namespace ACT\Actadmin\Actions;

class ViewAction extends AbstractAction
{
    public function getTitle()
    {
        return __('Actadmin::generic.view');
    }

    public function getIcon()
    {
        return 'actadmin-eye';
    }

    public function getPolicy()
    {
        return 'read';
    }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-sm btn-warning pull-right view',
        ];
    }

    public function getDefaultRoute()
    {
        return route('actadmin.'.$this->dataType->slug.'.show', $this->data->{$this->data->getKeyName()});
    }
}
