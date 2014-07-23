/**
 * Created by sachini on 7/23/14.
 */
(function($, Drupal) {
    Drupal.behaviors.rdfui = {
        attach: function(context, settings) {
            var options = settings.rdfui.options;
            alert(options);

            var availableTags = options;
            $( "#rdf-pred" ).autocomplete({
                source: availableTags
            });
        }

    }
})(jQuery, Drupal);
