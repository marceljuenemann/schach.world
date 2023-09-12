import ReactDOM from 'react-dom/client';

import { PairingListLoader } from './league/component/PairingList';
import { ReactElement } from 'react';
import { SortDivisionsDialog } from './league/component/SortDivisions';
import { CreateDivisionDialog } from './league/component/CreateDivision';
import { launchDialog } from './core/dialog';
import { UpdateTeamVenueDialog } from './league/component/UpdateTeamVenue';
import { UpdateTeamCaptainDialog } from './league/component/UpdateTeamCaptain';

/**
 * All elements with data-nsv-component will be rendered as a React component.
 */
$('[data-nsv-component]').each((_, elem: HTMLElement) => {
  ReactDOM.createRoot(elem).render(createComponent(elem));
})

function createComponent(elem: HTMLElement): ReactElement {
  switch (elem.getAttribute('data-nsv-component')) {
    case 'PairingList':
      const division = parseInt(elem.getAttribute('data-nsv-division') || '0')
      return <PairingListLoader division={division} />;
  }
  throw new Error('Invalid NSV component type');
}

/**
 * All elements with a data-nsv-dialog attribute will launch a React dialog.
 */
$('[data-nsv-dialog]').on('click', async event => {
  const elem: HTMLElement = event.currentTarget
  const result = await launchDialog(onClose => createDialogComponent(elem, onClose))
  // Possibly reload the page.
  if (result && elem.getAttribute('data-nsv-on-save') === 'reload') {
    window.location.reload()
  }
})

function createDialogComponent(elem: HTMLElement, onClose: () => void): ReactElement {
  const intAttr = (key: string) => parseInt(elem.getAttribute('data-' + key) as string)
  const type = elem.getAttribute('data-nsv-dialog')
  switch (type) {
    case 'SortDivisions':
      return <SortDivisionsDialog onClose={onClose} />;
    case 'CreateDivision':
      return <CreateDivisionDialog onClose={onClose} />;
    case 'UpdateTeamCaptain':
      return <UpdateTeamCaptainDialog onClose={onClose} teamId={intAttr('team-id')} />;
    case 'UpdateTeamVenue':
      return <UpdateTeamVenueDialog onClose={onClose} teamId={intAttr('team-id')} />;
    default:
      throw new Error(`Invalid NSV dialog type ${type}`);
  }
}
