import { Context } from "./context";

export class DialogContext {
  constructor(
    public readonly context: Context,
    public onClose: (val?: any) => any
  ) {}
}
