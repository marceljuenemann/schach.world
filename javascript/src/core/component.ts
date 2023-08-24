import React from "react";
import { Context } from "./context";
import { LeagueApi } from "../league/api";


/**
 * Abstract NSV root component with common utilities.
 */
export class NsvComponent<S = {}, P = {}> extends React.Component<P & {context: Context}, S> {

  protected leagueApi: LeagueApi

  constructor(props: P & {context: Context}) {
    super(props)
    this.leagueApi = new LeagueApi(this.props.context);
  }

  /**
   * Returns an attribute from the root data-nsv element that rendered this component. 
   */
  protected attribute(name: string) {
    return this.props.context.attribute(name)
  }
}
