var $collectionHolder;
var $impCollectionHolder;
// setup an "add a tag" link
var $addTagLink = $('<a href="#" onclick="doThisAfterInsert()" class="add_tag_link btn btn-default"><i class="fa fa-plus"></i>&nbsp;A&ntilde;adir Producto</a>');
var $newLinkLi = $('<p></p>').append($addTagLink);
//impuestos
var $addImpLink = $('<a href="#" onclick="doThisAfterInsert()" class="add_tag_link btn btn-default"><i class="fa fa-plus"></i>&nbsp;A&ntilde;adir Impuesto</a>');
var $newLinkTr = $('<p></p>').append($addImpLink);
jQuery(document).ready(function () {
// Get the ul that holds the collection of tags
    $collectionHolder = $('tbody.tags');
    $impCollectionHolder = $('tbody.imps');
    $collectionHolder.find('tr').each(function () {
        addTagFormDeleteLink($(this));
    });
    $impCollectionHolder.find('tr').each(function () {
        addImpFormDeleteLink($(this));
    });
// add the "add a tag" anchor and li to the tags ul
    $collectionHolder.append($newLinkLi);
    $impCollectionHolder.append($newLinkTr);
// count the current form inputs we have (e.g. 2), use that as the new
// index when inserting a new item (e.g. 2)
    $collectionHolder.data('index', $collectionHolder.find(':input').length);
    $impCollectionHolder.data('index', $impCollectionHolder.find(':input').length);
    $addTagLink.on('click', function (e) {
// prevent the link from creating a "#" on the URL
        e.preventDefault();
// add a new tag form (see next code block)
        addTagForm($collectionHolder, $newLinkLi);
    });

    $addImpLink.on('click', function (e) {
// prevent the link from creating a "#" on the URL
        e.preventDefault();
// add a new tag form (see next code block)
        addImpForm($impCollectionHolder, $newLinkTr);
    });
});

function addTagForm($collectionHolder, $newLinkLi) {
// Get the data-prototype explained earlier
    var prototype = $collectionHolder.data('prototype');
// get the new index
    var index = $collectionHolder.data('index');
// Replace '__name__' in the prototype's HTML to
// instead be a number based on how many items we have
    var newForm = prototype.replace(/__name__/g, index);
// increase the index with one for the next item
    $collectionHolder.data('index', index + 1);
// Display the form in the page in an li, before the "Add a tag" link li
    var $newFormLi = $('<tr></tr>').append(newForm);
    $newLinkLi.before($newFormLi);
    // add a delete link to the new form
    addTagFormDeleteLink($newFormLi);
}

function addImpForm($impCollectionHolder, $newLinkTr) {
// Get the data-prototype explained earlier
    var prototype = $impCollectionHolder.data('prototype');
// get the new index
    var index = $impCollectionHolder.data('index');
// Replace '__name__' in the prototype's HTML to
// instead be a number based on how many items we have
    var newForm = prototype.replace(/__name__/g, index);
// increase the index with one for the next item
    $impCollectionHolder.data('index', index + 1);
// Display the form in the page in an li, before the "Add a tag" link li
    var $newFormTr = $('<tr></tr>').append(newForm);
    $newLinkTr.before($newFormTr);
    // add a delete link to the new form
    addImpFormDeleteLink($newFormTr);
}

function addTagFormDeleteLink($tagFormLi) {
    var $removeFormA = $('<td><a title="Eliminar producto" href="#" class="btn btn-default"><em class="fa fa-minus-circle"></em></a></td>');
    $tagFormLi.append($removeFormA);
    $removeFormA.on('click', function (e) {
// prevent the link from creating a "#" on the URL
        e.preventDefault();
// remove the li for the tag form
        $tagFormLi.remove();
    });
}

function addImpFormDeleteLink($impFormLi) {
    var $removeFormA = $('<td></td><td></td><td><a title="Eliminar impuesto" href="#" class="btn btn-default"><em class="fa fa-minus-circle"></em></a></td>');
    $impFormLi.append($removeFormA);
    $removeFormA.on('click', function (e) {
// prevent the link from creating a "#" on the URL
        e.preventDefault();
// remove the li for the tag form
        $impFormLi.remove();
    });
}