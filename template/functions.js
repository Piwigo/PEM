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

function display(elementId)
{
  var element = document.getElementById(elementId);
  element.style.display = 'block';
}

function hide(elementId)
{
  var element = document.getElementById(elementId);
  element.style.display = 'none';
}

function revToggleDisplay(headerId, contentId)
{
  var revHeader = document.getElementById(headerId);
  var revContent = document.getElementById(contentId);

  if (revContent.style.display == 'none')
  {
    revContent.style.display = 'block';
    revHeader.className = 'changelogRevisionHeaderExpanded';
  }
  else
  {
    revContent.style.display = 'none';
    revHeader.className = 'changelogRevisionHeaderCollapsed';
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
      var revHeader;
      var revContent;

      for (var j = 0; j < revision.childNodes.length; j++)
      {
        var element = revision.childNodes[j];

        if (element.className == 'changelogRevisionHeaderExpanded'
            || element.className == 'changelogRevisionHeaderCollapsed')
        {
          revHeader = element;
        }

        if (element.className == 'changelogRevisionContent')
        {
          revContent = element;

          if (toggleStatus == 'collapsed')
          {
            revContent.style.display = 'block';
            revHeader.className = 'changelogRevisionHeaderExpanded';
          }
          else
          {
            revContent.style.display = 'none';
            revHeader.className = 'changelogRevisionHeaderCollapsed';
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