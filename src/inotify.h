#define FFI_SCOPE "INOTIfY"
#define FFI_LIB "libc.so.6"

/* Structure describing an inotify event.  */
struct inotify_event
{
  int wd;		/* Watch descriptor.  */
  uint32_t mask;	/* Watch mask.  */
  uint32_t cookie;	/* Cookie to synchronize two events.  */
  uint32_t len;		/* Length (including NULs) of name.  */
  char name[0];	/* Name.  */
};


/* Create and initialize inotify instance.  */
extern int inotify_init (void);

/* Create and initialize inotify instance.  */
extern int inotify_init1 (int __flags);

/* Add watch of object NAME to inotify instance FD.  Notify about events specified by MASK.  */
extern int inotify_add_watch (int __fd, const char *__name, uint32_t __mask);

/* Remove the watch specified by WD from the inotify instance FD.  */
extern int inotify_rm_watch (int __fd, int __wd);

extern int close(int fd);

extern int read(int fd, void *buf, size_t count);

extern int errno;
