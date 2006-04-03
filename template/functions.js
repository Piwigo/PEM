function display_changelog( revision_id )
{
  var element = document.getElementById( 'changelog_' + revision_id );
  
  if( element.style.display == 'none' )
  {
    element.style.display = 'block';
  }
  else
  {
    element.style.display = 'none';
  }
}

function confirm_del()
{
  return confirm( 'Êtes-vous sûr de vouloir supprimer cet élément ?' );
}