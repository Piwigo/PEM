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

function toggleDisplay(elementId)
{
  var element = document.getElementById(elementId);

  if (element.style.display == 'none')
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
  return confirm( 'Are you sure you want to delete this item?' );
}

var toggleStatus = 'collapsed';

function fullToggleDisplay()
{
  var changelog = document.getElementById('changelog');

  for (var i = 0; i < changelog.childNodes.length; i++)
  {
    var revision = changelog.childNodes[i];

    if (revision.className == 'changelogRevision')
    {
      for (var j = 0; j < revision.childNodes.length; j++)
      {
        var element = revision.childNodes[j];

        if (element.className == 'changelogRevisionContent')
        {
          if (toggleStatus == 'collapsed')
          {
            element.style.display = 'block';
          }
          else
          {
            element.style.display = 'none';
          }
        }
      }

    }
  }

  if (toggleStatus == 'collapsed')
  {
    toggleStatus = 'expanded';
  }
  else
  {
    toggleStatus = 'collapsed';
  }
}