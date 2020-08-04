<?php

namespace ACT\Actadmin\Actions;

class EditAction extends AbstractAction
{
    public function getTitle()
    {
        return __('Actadmin::generic.edit');
    }

    public function getIcon()
    {
        return 'actadmin-edit';
    }

    public function getPolicy()
    {
        return 'edit';
    }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-sm btn-primary pull-right edit',
        ];
    }

    public function getDefaultRoute()
    {
        return route('actadmin.'.$this->dataType->slug.'.edit', $this->data->{$this->data->getKeyName()});
    }
}
