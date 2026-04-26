import { PlayerData } from "./player-data/player-data.component"
import { Config, GroupConfig, Player } from "./types"

/**
 * Immutable representation of tournament data.
 */
export class Tournament {
  public readonly groups: Map<string, Group> = new Map()
  public readonly constraints: { groups: string[], availableSlots: number }[]
  public readonly waitlist: Player[]

  constructor(
    public readonly config: Config,
    public readonly players: Player[]
  ) {
    for (const groupConfig of config.groups) {
      this.groups.set(groupConfig.id, new Group(this, groupConfig))
    }
    // Calculate available slots for cross-group constraints.
    this.constraints = (config.constraints || []).map(constraint => {
      const groups = constraint.groups.map(id => this.groups.get(id))
      const playerCount = groups.reduce((sum, group) => sum + (group?.players.length || 0), 0)
      return {
        groups: constraint.groups,
        availableSlots: constraint.maxPlayers ? Math.max(0, constraint.maxPlayers - playerCount) : Infinity
      }
    })
    // Generate waitlist.
    this.waitlist = players
      .filter(p => p.waitlist)
      .sort((a, b) => (a.created || "") < (b.created || "") ? -1 : 1)
  }

  get availableSlots() {
    if (!this.config.maxPlayers) return Infinity
    return Math.max(this.config.maxPlayers - this.players.length, 0)
  }

  get registrationStarted() {
    if (!this.config.registrationStart) return true
    return new Date() >= new Date(this.config.registrationStart)
  }

  get deadlinePassed() {
    let deadline = new Date(this.config.deadline)
    deadline.setDate(deadline.getDate() + 1)
    return new Date() >= deadline
  }

  hasPlayer(player: Pick<PlayerData, 'name' | 'zps' | 'memberId'>): boolean {
    return this.players.some(p => {
      if (player.zps && player.memberId) {
        return p.playerData.zps === player.zps && p.playerData.memberId === player.memberId;
      } else {
        return p.playerData.name === player.name
      }
    });
  }

  /**
   * Returns the only group of the tournament, if there is exactly one group.
   */
  get singleGroup(): Group | null {
    if (this.groups.size == 1) {
      return this.groups.values().next().value!
    }
    return null
  }
}

export class Group {
  public readonly players: Player[]

  constructor(
    public readonly tournament: Tournament,
    public readonly config: GroupConfig
  ) {
    this.players = tournament.players
      .filter(p => p.group === this.id && !p.waitlist)
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
    for (const constraint of this.tournament.constraints) {
      if (constraint.groups.includes(this.id)) {
        availableSlots = Math.min(availableSlots, constraint.availableSlots)
      }
    }
    return availableSlots
  }
}
