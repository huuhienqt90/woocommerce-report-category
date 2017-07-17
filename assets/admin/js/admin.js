jQuery( document).ready( function($){
    $( ".izw-pro-all").click(function (){
        $('#izw-promoter option').attr('selected', 'selected');
        $('#izw-promoter').trigger('chosen:updated');
        return false;
    } );
    $( ".izw-pro-none").click(function (){
        $('#izw-promoter option:selected').removeAttr('selected');
        $('#izw-promoter').trigger('chosen:updated');
        return false;
    } );
});