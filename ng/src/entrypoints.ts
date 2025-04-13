import { Component } from "@angular/core";
import { TeamNameDialog } from "./league/team-name-dialog/team-name-dialog.component";

/**
 * Dialogs that can be launched via <nsv-dialog-launcher> component.
 */
export const DIALOG_COMPONENTS: Record<string, any> = {
  'teamName': TeamNameDialog
}
