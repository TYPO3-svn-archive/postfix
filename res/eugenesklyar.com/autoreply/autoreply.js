/* Auto Reply tab */

if (window.rcmail) {
  rcmail.addEventListener('init', function(evt) {
    
    // Define Variables
    var tab = $('<span>').attr('id', 'settingstabpluginautoreply').addClass('tablink');
    var button = $('<a>').attr('href', rcmail.env.comm_path + '&_action=plugin.autoreply').html(rcmail.gettext('autoreply', 'autoreply')).appendTo(tab);
    
    button.bind('click', function(e){ return rcmail.command('plugin.autoreply', this) });
    
    if (tab)
        tab.className = 'tablink-selected';

    // Button & Register commands
    rcmail.add_element(tab, 'tabs');
    rcmail.register_command('plugin.autoreply', function() { rcmail.goto_url('plugin.autoreply') }, true);
    rcmail.register_command('plugin.autoreply-save', function() { 
        var input_subject = rcube_find_object('_subject');
        var input_message = rcube_find_object('_html_message');
        
        if (input_subject && input_subject.value == '') {
          alert(rcmail.gettext('missing_subject', 'autoreply'));
          input_subject.focus();
        } else if (input_message && input_message.value == '') {
          alert(rcmail.gettext('missing_message', 'autoreply'));
          input_message.focus();
        } else {
          rcmail.gui_objects.autoreplyform.submit();
        }
    }, true);
  });
}
