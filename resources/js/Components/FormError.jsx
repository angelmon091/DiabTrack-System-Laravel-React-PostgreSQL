export default function FormError({ message, id, className = '' }) {
    if (!message) {
        return null;
    }

    const messages = Array.isArray(message) ? message : [message];

    return (
        <ul
            id={id}
            className={`mt-1 list-none space-y-1 text-sm text-red-600 ${className}`}
            role="alert"
        >
            {messages.map((item) => (
                <li key={item}>{item}</li>
            ))}
        </ul>
    );
}
