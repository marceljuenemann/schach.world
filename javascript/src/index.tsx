import ReactDOM from 'react-dom/client';

import { PairingList, PairingListLoader } from './league/component/PairingList';
import { ReactElement } from 'react';
import { SortDivisions } from './league/component/SortDivisions';
import { CreateDivision } from './league/component/CreateDivision';
import { launchDialog } from './core/dialog';

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
  const elem: HTMLElement = event.target
  const result = await launchDialog(onClose => createDialogComponent(elem, onClose))
  // Possibly reload the page.
  if (result && elem.getAttribute('data-nsv-on-save') === 'reload') {
    window.location.reload()
  }
})

function createDialogComponent(elem: HTMLElement, onClose: () => void): ReactElement {
  const type = elem.getAttribute('data-nsv-dialog')
  switch (type) {
    case 'SortDivisions':
      return <SortDivisions onClose={onClose} />;
    case 'CreateDivision':
      return <CreateDivision onClose={onClose} />;
    default:
      throw new Error(`Invalid NSV dialog type ${type}`);
  }
}
