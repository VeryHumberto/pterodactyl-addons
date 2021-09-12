Enjoy your add-on, be patient.  
I recommend installing  [Typora](https://typora.io/) for the best experience of reading.    
If you run into an issue please contact me.

# Installation

## Panel

You need to redo all steps, after every panel update.
You can use the file given in this archive as an example, where to paste the code snippets.

**Easy installtion:** 
Overwrite the file given in this archive, to 
`resources/scripts/components/server/files/UploadButton.tsx`

**Manual Installation:**
1. Open file `resources/scripts/components/server/files/UploadButton.tsx`
below `import { WithClassname } from '@/components/types';`, add
`import { bytesToHuman } from "@/helpers";`

2. On the same file, below `const [loading, setLoading] = useState(false);`
add `const [upload, setUpload] = useState({ size: 0, totalSize: 0, progress: 0 });`

3. On the same file, `headers` comma
```react
headers: {
    'Content-Type': 'multipart/form-data',
}, //<!-- this comma, paste here the below onUploadProgress
onUploadProgress: (progressEvent: ProgressEvent) => {
    const size = progressEvent.loaded;
    const totalSize = progressEvent.total;
    const progress = Math.round((progressEvent.loaded / progressEvent.total) * 100);
    setUpload({ size, totalSize, progress });
},
```

4. On the same file, at `<SpinnerOverlay visible={loading} size={'large'} fixed></SpinnerOverlay>` replace it with
```react
<SpinnerOverlay visible={loading} size={'large'} fixed>
    <span css={tw`mt-4`}>Uploaded {bytesToHuman(upload.size)} of {bytesToHuman(upload.totalSize)} ({upload.progress}%)</span>
</SpinnerOverlay>
```

### SSH - Commands

---

You can check with the command `node -v` to see if you have [**NodeJS**](https://pterodactyl.io/community/customization/panel.html#install-dependencies) installed.  
If you don't have it, follow the below tutorial to install the [**NodeJS**](https://pterodactyl.io/community/customization/panel.html#install-dependencies).  


<img src="assets/install_nodejs.svg" alt="Installation Guide" title="That's commands should be executed depending on your OS" style="zoom:50%;" />

1. `cd /var/www/pterodactyl`
2. `npm install -g yarn`
3. `yarn install`
4. `yarn build:production`

# Contact
- Discord: **LocalHost#8547**