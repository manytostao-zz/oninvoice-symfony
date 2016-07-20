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
var $clientesTable = {};


$clientesTable.postDraw = function () {
    var $tr = $('#clientesTable').find('tbody tr');
    $tr.each(function () {
        var aData = $('#clientesTable').dataTable().fnGetData(this);
        var $column = $('<td></td>');
        var $btnGroup = $('<div class="btn-group btn-group-justified" style="text-align: center"></div>');

        //Buttons
        var $btnDetalles = $('<a class="edit" title="Detalles" href="#">');
        var $innerDiv1 = $('<div class="btn btn-sm btn-default"></div>');
        $innerDiv1.append($('<i class="fa fa-search"></i>'));
        $btnDetalles.append($innerDiv1);
        $btnDetalles.attr('data-id', aData != null ? aData[0] : null);
        $btnDetalles.attr('data-active', aData != null ? aData[2] : null);
        var $parent1 = $(this).closest('tr');
        var $text1 = $parent1.find('.nom-description');
        $btnDetalles.attr('data-name', $text1.html());

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

        $btnGroup.append($btnDetalles).append($btnEditar).append($btnEliminar);

        if (aData != null && aData[5] == 'No') {
            var $subcontainer3 = $('<div class="col-md-4"></div>');
            var $btnActivar = $('<a title="Activar" href="#activate" data-toggle="modal"></a>');
            var $innerDiv4 = $('<div class="btn blue-hoki btn-small"></div>');
            $innerDiv4.append($('<i class="fa fa-check-square-o"></i>'));
            $btnActivar.append($innerDiv4);
            $btnActivar.attr('data-toggle', 'modal');
            $btnActivar.attr('data-target', '#confirm');
            $btnActivar.attr('data-id', aData != null ? aData[0] : null);
            $subcontainer3.append($btnActivar);
        }
        $column.append($btnGroup);
        $(this).append($column);

        //Events
        //Edit
        $btnEditar.click(function () {
            document.location = Routing.generate('clientes_edit', {'id':$(this).data('id')});
        });

        //Details
        $btnDetalles.click(function () {
            document.location = Routing.generate('clientes_details', {'id':$(this).data('id')});
        });

        //Delete
        $btnEliminar.click(function () {
            var id = $(this).attr('data-id');
            $('#deleteButton').attr('href', Routing.generate('clientes_delete', {id: id}));
            var $modalView = $('#delete');
            $modalView.modal();
        });

    });

};
