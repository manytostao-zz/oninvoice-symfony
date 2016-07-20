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
var $gruposTable = {};


$gruposTable.postDraw = function () {
    var $tr = $('#gruposTable').find('tbody tr');
    $tr.each(function () {
        var aData = $('#gruposTable').dataTable().fnGetData(this);
        var $column = $('<td></td>');
        var $btnGroup = $('<div class="btn-group btn-group-justified" style="text-align: center"></div>');

        //Buttons

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
        $btnGroup.append($btnEditar).append($btnEliminar);
        $column.append($btnGroup);
        $(this).append($column);

        //Events
        //Edit
        $btnEditar.click(function () {
            document.location = Routing.generate('nomencladores_editGrupo', {'id': $(this).data('id')});
        });

        //Delete
        $btnEliminar.click(function () {
            var id = $(this).attr('data-id');
            $('#deleteButton').attr('href', Routing.generate('nomencladores_deleteGrupo', {id: id}));
            var $modalView = $('#delete');
            $modalView.modal();
        });
    });
};
