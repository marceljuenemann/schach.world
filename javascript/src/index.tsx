import ReactDOM from 'react-dom/client';

import { PairingList } from './league/component/PairingList';
import { Context } from './context';
import { ReactElement } from 'react';

$('[data-nsv-component]').each((_, elem: HTMLElement) => {
  const context = new Context(window, elem);
  ReactDOM.createRoot(elem).render(createComponent(context));
});

function createComponent(context: Context): ReactElement {
  switch (context.attribute('component')) {
    case 'PairingList':
      return <PairingList context={context} />;
  }
  throw 'Invalid NSV component type';
}
