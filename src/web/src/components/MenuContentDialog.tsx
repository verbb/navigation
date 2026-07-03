import { useEffect } from 'react';

type Props = {
  open: boolean;
  menuId: number;
  siteId: number;
  onOpenChange: (open: boolean) => void;
  onSaved: () => void;
};

type CpScreenSlideout = {
  open: () => void;
  close: () => void;
  destroy: () => void;
  on: (event: string, handler: () => void) => void;
};

export function MenuContentDialog({ open, menuId, siteId, onOpenChange, onSaved }: Props) {
  useEffect(() => {
    if (!open) {
      return;
    }

    const Craft = (window as typeof window & {
      Craft: {
        CpScreenSlideout: new (action: string, settings?: Record<string, unknown>) => CpScreenSlideout;
      };
    }).Craft;

    const slideout = new Craft.CpScreenSlideout('navigation/builder/menu-content-slideout', {
      params: {
        menuId,
        siteId,
      },
      onSubmit: () => {
        onSaved();
      },
    });

    slideout.on('close', () => {
      onOpenChange(false);
    });

    slideout.open();

    return () => {
      slideout.destroy();
    };
  }, [open, menuId, siteId, onOpenChange, onSaved]);

  return null;
}
