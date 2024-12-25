import { Config, GroupConfig, Player } from "./types"

/**
 * Immutable representation of tournament data.
 */
export class Tournament {
  public readonly groups: Map<string, Group> = new Map()

  constructor(
    public readonly config: Config,
    public readonly players: Player[]
  ) {
    for (const groupConfig of config.groups) {
      this.groups.set(groupConfig.id, new Group(this, groupConfig))
    }
  }
}

export class Group {

  constructor(
    public readonly tournament: Tournament,
    public readonly config: GroupConfig
  ) {}

  get id() {
    return this.config.id
  }

  get name() {
    return this.config.name
  }

}
