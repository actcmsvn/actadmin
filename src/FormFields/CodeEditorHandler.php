<?php

namespace ACT\Actadmin\FormFields;

class CodeEditorHandler extends AbstractHandler
{
    protected $codename = 'code_editor';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('Actadmin::formfields.code_editor', [
            'row'             => $row,
            'options'         => $options,
            'dataType'        => $dataType,
            'dataTypeContent' => $dataTypeContent,
        ]);
    }
}
