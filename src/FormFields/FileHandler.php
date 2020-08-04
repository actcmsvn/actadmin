<?php

namespace ACT\Actadmin\FormFields;

class FileHandler extends AbstractHandler
{
    protected $codename = 'file';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('Actadmin::formfields.file', [
            'row'             => $row,
            'options'         => $options,
            'dataType'        => $dataType,
            'dataTypeContent' => $dataTypeContent,
        ]);
    }
}
