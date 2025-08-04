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

  get availableSlots() {
    if (!this.config.maxPlayers) return Infinity
    return Math.max(this.config.maxPlayers - this.players.length, 0)
  }

  get deadlinePassed() {
    let deadline = new Date(this.config.deadline)
    deadline.setDate(deadline.getDate() + 1)
    return new Date() >= deadline
  }
}

export class Group {
  public readonly players: Player[]

  constructor(
    public readonly tournament: Tournament,
    public readonly config: GroupConfig
  ) {
    this.players = tournament.players
      .filter(p => p.group === this.id)
      .sort((a, b) => {
        if (a.playerData.dwz !== b.playerData.dwz) {
          return (b.playerData.dwz ?? 0) - (a.playerData.dwz ?? 0);
        }
        return a.playerData.name.localeCompare(b.playerData.name);
      });
  }

  get id() {
    return this.config.id
  }

  get name() {
    return this.config.name
  }

  get availableSlots() {
    let availableSlots = this.tournament.availableSlots
    if (this.config.maxPlayers) {
      availableSlots = Math.min(Math.max(this.config.maxPlayers - this.players.length, 0), availableSlots)
    }
    return availableSlots
  }

  mayRegister(playerData: PlayerData): boolean {
    if (this.config.minDwz && (playerData.dwz || 0) < this.config.minDwz) return false
    if (this.config.maxDwz && (playerData.dwz || 0) > this.config.maxDwz) return false
    if (this.config.minYearOfBirth && (playerData.yearOfBirth || Infinity) < this.config.minYearOfBirth) return false
    return !!this.availableSlots
  }
}
