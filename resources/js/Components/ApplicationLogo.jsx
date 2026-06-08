export default function ApplicationLogo(props) {
    return (
        <img 
            {...props} 
            src="/vidflow-icon.png" 
            alt="VidFlow Logo" 
            style={{ objectFit: 'contain' }}
        />
    );
}
