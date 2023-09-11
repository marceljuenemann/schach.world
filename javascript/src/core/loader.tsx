import React, { ReactNode } from "react";
import { Spinner } from "react-bootstrap";

/**
 * Abstract loader component that shows a spinner while the target component is loaded.
 */
export abstract class LoadingComponent<P = {}> extends React.Component<P, {loadedComponent?: ReactNode}> {

  constructor(props: P) {
    super(props)
    this.state = {}
  }

  protected abstract loadComponent(): Promise<ReactNode>

  async componentDidMount() {
    const loadedComponent = await this.loadComponent()
    // TODO: Only setState if component didn't unmount in the meantime.
    this.setState({loadedComponent})    
  }

  render() {
    return this.state.loadedComponent || <Spinner animation="border" role="status"></Spinner>
  }
}
