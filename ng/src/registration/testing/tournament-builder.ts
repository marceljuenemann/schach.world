import { Tournament } from "../tournament";
import { Config, GroupConfig, Player } from "../types";

import * as TEST_CONFIG from "./test-config.json";

export const TEST_PLAYER: Player = {
  id: 1,
  group: "A",
  playerData: {
    name: "Jünemann, Marcel",
    club: "SK Lehrte",
    zps: '70156',
    memberId: '0116',
    gender: 'M',
    yearOfBirth: 2000,
    dwz: 1600,
    elo: 1750,
    fideTitle: null,
    fideId: 42943253
  },
  contactDetails: {
    name: "Marcel Jünemann",
    email: "marcel@example.com"
  }
}

export class TournamentBuilder {
  private _config: Config = TEST_CONFIG as Config;
  private _players: Player[] = [];

  config(config: Partial<Config>): TournamentBuilder {
    this._config = { ...this._config, ...config };
    return this;
  }

  groupConfig(id: string, config: Partial<GroupConfig>): TournamentBuilder {
    const existingGroup = this._config.groups.find(g => g.id === id);
    if (existingGroup) {
      Object.assign(existingGroup, config);
    }
    return this;
  }

  addPlayer(player: Partial<Player>): TournamentBuilder {
    this._players.push({ ...TEST_PLAYER, ...player });
    return this;
  }

  addPlayers(playerCounts: Record<string, number>): TournamentBuilder {
    for (const [group, count] of Object.entries(playerCounts)) {
      for (let i = 0; i < count; i++) {
        this.addPlayer({ group });
      }
    }
    return this;
  }

  build(): Tournament {
    return new Tournament(this._config, this._players);
  }
}
