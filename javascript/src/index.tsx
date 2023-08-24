import ReactDOM from 'react-dom/client';

import { PairingList } from './league/component/PairingList';
import { Context } from './core/context';
import { ReactElement } from 'react';
import { SortDivisions } from './league/component/SortDivisions';
import { DialogContext } from './core/dialog';

/**
 * All elements with data-nsv-component will be rendered as a React component.
 */
$('[data-nsv-component]').each((_, elem: HTMLElement) => {
  const context = new Context(window, elem);
  ReactDOM.createRoot(elem).render(createComponent(context));
})

/**
 * All elements with a data-nsv-dialog attribute will launch a React dialog.
 */
$('[data-nsv-dialog]').on('click', event => {
  const context = new DialogContext(window, event.target)
  launchDialog(context.attribute('dialog')!!, context)
})

function createComponent(context: Context): ReactElement {
  switch (context.attribute('component')) {
    case 'PairingList':
      return <PairingList context={context} />;
  }
  throw new Error('Invalid NSV component type');
}

// TODO: Maybe this is overkill and we should just render the button that
// launches the dialog in React as well?
function launchDialog(type: string, context: DialogContext) {
  // Create container div for rendering the dialog.
  const container = $("<div>")
  $("body").append(container);

  // Render the dialog.
  const component = createDialogComponent(type, context)
  const root = ReactDOM.createRoot(container[0])
  root.render(component);

  // Handle onClose callback
  // TODO: This is a bit hacky...
  context.onClose = (val: any) => {
    // TODO: return value as a Promise
    root.unmount()
    container.remove() 
  }
}

function createDialogComponent(type: string, context: DialogContext) {
  switch (type) {
    case 'SortDivisions':
      return <SortDivisions context={context} />;
  }
  throw new Error('Invalid NSV dialog type');
}
