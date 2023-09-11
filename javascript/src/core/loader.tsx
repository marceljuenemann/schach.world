import React, { ReactNode } from "react";
import { Spinner } from "react-bootstrap";

/**
 * Abstract loader component that shows a spinner while props for the target component are loaded.
 */
export abstract class LoadingComponent<L = {}, P = {}> extends React.Component<P, {loadedProps?: L}> {

  constructor(props: P) {
    super(props)
    this.state = {}
  }

  abstract loadProps(): Promise<L>
  abstract renderWithProps(props: L): ReactNode

  async componentDidMount() {
    const loadedProps = await this.loadProps()
    // TODO: Only setState if component didn't unmount in the meantime.
    this.setState({loadedProps})
  }

  render() {
    if (!this.state.loadedProps) {
      return <Spinner animation="border" role="status"></Spinner>
    }
    return this.renderWithProps(this.state.loadedProps)
  }
}
