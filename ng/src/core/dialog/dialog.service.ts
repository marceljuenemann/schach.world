import { Injectable, Injector } from '@angular/core';
import { NgbModal, NgbModalOptions, NgbModalRef } from '@ng-bootstrap/ng-bootstrap';
import { DIALOG_PARAMS } from './dialog';
import { ConfirmationDialogComponent, ConfirmationDialogParams } from './confirmation/confirmation-dialog.component';

@Injectable({
  providedIn: 'root'
})
export class DialogService {

  constructor(private injector: Injector, private modalService: NgbModal) { }

  open<TParams>(component: any, params: TParams, options: NgbModalOptions = {}): NgbModalRef {
    options.centered = true
    options.injector = Injector.create({
      providers: [{
        provide: DIALOG_PARAMS, useValue: params
      }],
      parent: this.injector
    })
    return this.modalService.open(component, options)
  }

  confirm<T>(params: ConfirmationDialogParams<T>): Promise<T> {
    return this.open(ConfirmationDialogComponent, params).result
  }
}
