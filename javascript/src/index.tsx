import React from 'react';
import ReactDOM from 'react-dom/client';

import PairingList from './league/component/PairingList';

$('[data-nsv-component]').each((_, elem) => {
  let type = elem.getAttribute('data-nsv-type');
  let param = (name: string) => {
    return elem.getAttribute(`data-nsv-${name}`);
  } 

  switch (type) {
    case 'PairingList':
      render(elem, <PairingList division={param('division')} />);
      break;
  }
});

function render(elem: HTMLElement, component: any) {
  ReactDOM.createRoot(elem).render(component);
}
