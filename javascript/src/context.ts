
/**
 * The context in which a component is rendered includes information
 * like the root element and accessors for attributes on that. The
 * main purpose of this class is to make the code more testable.
 */
export class Context {
  constructor(private window: Window, private rootElement: HTMLElement) {}

  attribute(name: string): string|null {
    return this.rootElement.getAttribute(`data-nsv-${name}`);
  }
}
