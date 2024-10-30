function quickEditCP() {
    var $ = jQuery;
    var _edit = inlineEditPost.edit;
    inlineEditPost.edit = function(id) {
        var args = [].slice.call(arguments);
        _edit.apply(this, args);

        if (typeof(id) == 'object') {
            id = this.getId(id);
        }
        //if (this.type == 'post') {
            var
            // editRow is the quick-edit row, containing the inputs that need to be updated
            editRow = $('#edit-' + id),
            // postRow is the row shown when a post isn't being edited, which also holds the existing values.
            postRow = $('#post-'+id),

            // get the existing values
            // the class ".column-cp_notes" is set in columns
            notes = $('.column-cp_notes', postRow).text(),
            incomplete = $('.column-cp img', postRow).attr('data-value');

            // set the values in the quick-editor
            $(':input[name="_cp_notes"]', editRow).val(notes);
            $(':input[name="_cp_incomplete"]', editRow).val(incomplete);
        //}
    };
}
// Another way of ensuring inlineEditPost.edit isn't patched until it's defined
if (inlineEditPost) {
    quickEditCP();
} else {
    jQuery(quickEditCP);
}