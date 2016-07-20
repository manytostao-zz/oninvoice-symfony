/**
 * Created by manytostao on 25/05/15.
 */
'use strict';

/*
 * show.bs.modal
 * shown.bs.modal
 * hide.bs.modal
 * hidden.bs.modal
 *
 * */
var $monedasTable = {};


$monedasTable.postDraw = function () {
    var $tr = $('#monedasTable').find('tbody tr');
    var monedaBase = false;
    $.post(Routing.generate('appParam_monedaBase'))
        .done(function (data) {
            data = JSON.parse(data);
            monedaBase = data.monedaBase;
            $tr.each(function () {
                var aData = $('#monedasTable').dataTable().fnGetData(this);
                var $column = $('<td></td>');
                var $btnGroup = $('<div class="btn-group btn-group-justified" style="text-align: center"></div>');

                //Buttons
                /*Chequear si es moneda base*/

                if (monedaBase == aData[2]) {
                    var $label = $('<a><label class="label label-approved"> Moneda Base</label></a>');
                }

                var $btnEditar = $('<a class="edit" title="Editar" href="#">');
                var $innerDiv2 = $('<div class="btn btn-sm btn-default"></div>');
                $innerDiv2.append($('<i class="fa fa-edit"></i>'));
                $btnEditar.append($innerDiv2);
                $btnEditar.attr('data-id', aData != null ? aData[0] : null);
                $btnEditar.attr('data-active', aData != null ? aData[2] : null);
                var $parent2 = $(this).closest('tr');
                var $text2 = $parent2.find('.nom-description');
                $btnEditar.attr('data-name', $text2.html());

                var $btnEliminar = $('<a title="Eliminar" href="#delete" data-toggle="modal"></a>');
                var $innerDiv3 = $('<div class="btn btn-sm btn-default"></div>');
                $innerDiv3.append($('<i class="fa fa-trash-o"></i>'));
                $btnEliminar.append($innerDiv3);
                $btnEliminar.attr('data-toggle', 'modal');
                $btnEliminar.attr('data-target', '#confirm');
                $btnEliminar.attr('data-id', aData != null ? aData[0] : null);
                if (monedaBase == aData[2]) {
                    $btnGroup.append($label).append($btnEditar);
                } else
                    $btnGroup.append($btnEditar).append($btnEliminar);
                $column.append($btnGroup);
                $(this).append($column);

                //Events
                //Edit
                $btnEditar.click(function () {
                    document.location = Routing.generate('nomencladores_editMone', {'id': $(this).data('id')});
                });

                //Delete
                $btnEliminar.click(function () {
                    var id = $(this).attr('data-id');
                    $('#deleteButton').attr('href', Routing.generate('nomencladores_deleteMone', {id: id}));
                    var $modalView = $('#delete');
                    $modalView.modal();
                });
            });
        });
};
