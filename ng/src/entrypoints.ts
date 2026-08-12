import { Component } from "@angular/core";
import { TeamNameDialog } from "./league/team-name-dialog/team-name-dialog.component";
import { PlayerDialog } from "./league/player-dialog/player-dialog.component";
import { DeletePlayerDialog } from "./league/delete-player-dialog/delete-player-dialog.component";
import { ReorderPlayersDialog } from "./league/reorder-players-dialog/reorder-players-dialog.component";

/**
 * Dialogs that can be launched via <nsv-dialog-launcher> component.
 */
export const DIALOG_COMPONENTS: Record<string, any> = {
  'teamName': TeamNameDialog,
  'playerDialog': PlayerDialog,
  'deletePlayer': DeletePlayerDialog,
  'reorderPlayers': ReorderPlayersDialog
}
