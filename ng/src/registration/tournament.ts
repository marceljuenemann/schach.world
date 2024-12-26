import { PlayerData } from "./player-data/player-data.component"
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

  get players() {
    return this.tournament.players
      .filter(p => p.group === this.id)
      .sort((a, b) => {
        if (a.playerData.dwz !== b.playerData.dwz) {
          return (b.playerData.dwz ?? 0) - (a.playerData.dwz ?? 0);
        }
        return a.playerData.name.localeCompare(b.playerData.name);
      });
  }

  mayRegister(playerData: PlayerData): boolean {
    if (this.config.maxDwz && (playerData.dwz || 0) > this.config.maxDwz) return false
    if (this.config.minYearOfBirth && (playerData.yearOfBirth || Infinity) < this.config.minYearOfBirth) return false
    return true
  }
}
